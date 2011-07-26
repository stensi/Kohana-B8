<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Default storage class.
 *
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
class B8_Storage_Default extends Storage {

	// Configuration
	protected $config = array();

	// Degenerator
	protected $degenerator;

	// Words
	protected $words;

	/**
	 * Creates a new Storage_Default object.
	 *
	 * @param   array   configuration
	 * @return  void
	 */
	public function __construct(array $config = array())
	{
		// Load the storage default config file
		$this->config = Kohana::$config->load('b8.storage.default');

		// Overwrite with custom config settings
		foreach ($config as $key => $value)
		{
			$this->config[$key] = $value;
		}

		// Setup degenerator
		$this->degenerator = Degenerator::factory($this->config['use_degenerator']);
	}

	/**
	 * Get total counts of learned ham or spam texts.
	 *
	 * @param   string  classification category
	 * @return  int     total texts learned by category
	 */
	public function get_total($category)
	{
		$result = DB::select('total')
			->from($this->config['category_table'])
			->where('category', '=', $category)
			->execute($this->config['database'])
			->current();

		return empty($result['total']) ? 0 : $result['total'];
	}

	/**
	 * Process words
	 *
	 * @param   array   words to process
	 * @return  bool
	 */
	public function process_words(array $words)
	{
		$stored_words               = array();
		$missing_words              = array();
		$final_words                = array();
		$final_words['words']       = array();
		$final_words['degenerates'] = array();

		// Get stored words
		$results = DB::select('word', B8::HAM, B8::SPAM)
			->from($this->config['word_table'])
			->where('word', 'IN', $words)
			->execute($this->config['database'])
			->as_array();

		// Standardise stored words
		foreach ($results as $result)
		{
			$stored_words[$result['word']] = array(B8::HAM => $result[B8::HAM], B8::SPAM => $result[B8::SPAM]);
		}
		
		// Compare words to process with stored words
		foreach ($words as $word)
		{
			// Add word to missing words if a stored word was not found
			if ( ! isset($stored_words[$word]))
			{
				$missing_words[] = $word;
			}
		}

		// Degenerate missing words
		if ( ! empty($missing_words))
		{
			$degenerate_words = array();
			$degenerates = $this->degenerator->degenerate($missing_words);

			// Get the full list of degenerate words
			foreach ($degenerates as $word => $word_degenerates)
			{
				$degenerate_words = array_merge($degenerate_words, $word_degenerates);
			}

			// Get stored degenerate words
			$results = DB::select('word', B8::HAM, B8::SPAM)
				->from($this->config['word_table'])
				->where('word', 'IN', $degenerate_words)
				->execute($this->config['database'])
				->as_array();

			// Standardise stored degenerate words
			foreach ($results as $result)
			{
				$stored_words[$result['word']] = array(B8::HAM => $result[B8::HAM], B8::SPAM => $result[B8::SPAM]);
			}
		}

		// Stored words now has all words
		foreach ($words as $word)
		{
			// An exact word match was found
			if (isset($stored_words[$word]))
			{
				$final_words['words'][$word] = $stored_words[$word];
			}
			// An exact word match was not found
			else
			{
				// Check the degenerated words
				foreach ($this->degenerator->degenerates[$word] as $degenerate)
				{
					// A degenerate word match was found
					if (isset($stored_words[$degenerate]))
					{
						$final_words['degenerates'][$word][$degenerate] = $stored_words[$degenerate];
					}
				}
			}
		}

		return $final_words;
	}

	/**
	 * Process text
	 *
	 * @param   string  words from text to process
	 * @param   string  classification category (B8::HAM or B8::SPAM)
	 * @param   string  action (B8::LEARN or B8::UNLEARN)
	 * @return  bool
	 */
	public function process_text($words, $category, $action)
	{
		$stored_words = array();

		// Get total texts learned in ham and spam
		$total[B8::HAM] = $this->get_total(B8::HAM);
		$total[B8::SPAM] = $this->get_total(B8::SPAM);

		// Get stored words
		$results = DB::select('word', B8::HAM, B8::SPAM)
			->from($this->config['word_table'])
			->where('word', 'IN', array_keys($words))
			->execute($this->config['database'])
			->as_array();

		// Standardise stored words
		foreach ($results as $result)
		{
			$stored_words[$result['word']] = array(B8::HAM => $result[B8::HAM], B8::SPAM => $result[B8::SPAM]);
		}

		// Process words
		foreach ($words as $word => $count)
		{
			// Word exists in database
			if (isset($stored_words[$word]))
			{
				// Learn or Unlearn word
				switch ($action)
				{
					case B8::LEARN:
						$stored_words[$word][$category] += $count;
						break;

					case B8::UNLEARN:
						$stored_words[$word][$category] -= $count;
						break;
				}

				// Ensure category count does not fall below zero
				if ($stored_words[$word][$category] < 0)
				{
					$stored_words[$word][$category] = 0;
				}

				// Check if Update or Delete is required
				if ($stored_words[$word][B8::HAM] > 0 OR  $stored_words[$word][B8::SPAM] > 0)
				{
					// Update
					DB::update($this->config['word_table'])
						->set(array(B8::HAM => $stored_words[$word][B8::HAM], B8::SPAM => $stored_words[$word][B8::SPAM]))
						->where('word', '=', $word)
						->execute($this->config['database']);
				}
				else
				{
					// Delete
					DB::delete($this->config['word_table'])
						->where('word', '=', $word)
						->execute($this->config['database']);
				}
			}
			// Word does not exist in database
			else
			{
				// Insert on B8::LEARN as we don't want to insert on B8::UNLEARN
				if ($action == B8::LEARN)
				{
					// Check which category we're learning
					switch ($category)
					{
						case B8::HAM:
							$ham  = $count;
							$spam = 0;
							break;

						case B8::SPAM:
							$ham  = 0;
							$spam = $count;
							break;
					}

					// Insert
					DB::insert($this->config['word_table'], array('word', B8::HAM, B8::SPAM))
						->values(array($word, $ham, $spam))
						->execute($this->config['database']);
				}
			}
		}

		// Update total number of learned texts
		switch ($action)
		{
			case B8::LEARN:
				$total[$category]++;
				break;

			case B8::UNLEARN:
				if ($total[$category] > 0)
				{
					$total[$category]--;
				}
				break;
		}

		// Update
		DB::update($this->config['category_table'])
			->set(array('total' => $total[$category]))
			->where('category', '=', $category)
			->execute($this->config['database']);
	}
}
