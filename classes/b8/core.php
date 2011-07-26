<?php defined('SYSPATH') or die('No direct script access.');

/**
 * B8 is a port of Tobias Leupold's PHP5 b8 into a module for the Kohana 3 PHP Framework.
 *
 * The original b8 can be found at:
 * http://nasauber.de/opensource/b8/
 * 
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
class B8_Core {

	// Configuration
	protected $config = array();

	// Lexer
	protected $lexer;

	// Storage
	protected $storage;

	// Processed words
	protected $processed_words = array();

	// Constants
	const UNSURE  = 'unsure';
	const SPAM    = 'spam';
	const HAM     = 'ham';
	const LEARN   = 'learn';
	const UNLEARN = 'unlearn';

	/**
	 * Creates and returns a new B8.
	 *
	 * @param   array    configuration
	 * @return  B8
	 */
	public static function factory(array $config = array())
	{
		return new B8($config);
	}

	/**
	 * Creates a new B8 object.
	 *
	 * @param   array  custom configuration
	 * @return  void
	 */
	public function __construct(array $config = array())
	{
		// Load the lexer default config file
		$this->config = Kohana::$config->load('b8');

		// Overwrite with custom config settings
		foreach ($config as $key => $value)
		{
			$this->config[$key] = $value;
		}

		// Setup lexer, passing config settings through in case of custom configuration
		$this->lexer = Lexer::factory($this->config['use_lexer'], $this->config['lexer'][$this->config['use_lexer']]);

		// Setup storage, passing config settings through in case of custom configuration
		$this->storage = Storage::factory($this->config['use_storage'], $this->config['storage'][$this->config['use_storage']]);
	}

	/**
	 * Classify which category the text belongs to more.
	 * The value will be between 0 (ham) and 1 (spam).
	 *
	 * @param   string  text to classify
	 * @param   string  which type of classify return value to use
	 * @return  float   probability
	 */
	public function classify($text, $use_classify = NULL)
	{
		// Get total texts learned in ham and spam
		$total[self::HAM] = $this->storage->get_total(self::HAM);
		$total[self::SPAM] = $this->storage->get_total(self::SPAM);

		// Get words from text
		$words = $this->lexer->get_words($text);

		// No words to process
		if (empty($words))
		{
			return $this->config['rob_x'];
		}

		// Process words
		$this->processed_words = $this->storage->process_words(array_keys($words));

		$word_occurance   = array();
		$word_probability = array();
		$word_relevance   = array();

		// Process words
		foreach ($words as $word => $count)
		{
			// Number of occurances
			$word_occurance[$word] = $count;

			// Check if word was found during storage processing
			if (isset($this->processed_words['words'][$word]))
			{
				// Probability of word being spam
				$word_probability[$word] = $this->calculate_probability($this->processed_words['words'][$word], $total);
			}
			// Check if degenerated words were found during storage processing
			else if (isset($this->processed_words['degenerates'][$word]))
			{
				// Start with a neutral probability;
				$probability = 0.5;

				// Use the degenerate probability with the greatest distance from 0.5
				foreach($this->processed_words['degenerates'][$word] as $degenerate => $counts)
				{
					// Calculate probability for current degenerated word
					$degenerate_probability = $this->calculate_probability($counts, $total);

					// Use this degenerate probability if its distance is greater
					if (abs(0.5 - $degenerate_probability) > abs(0.5 - $probability))
					{
						$probability = $degenerate_probability;
					}
				}

				// Probability of word being spam
				$word_probability[$word] = $probability;
			}
			else
			{
				// Probability of word being spam
				$word_probability[$word] = $this->config['rob_x'];
			}

			// The greater the distance from 0.5, the more relevant
			$word_relavance[$word] = abs(0.5 - $word_probability[$word]);
		}

		// Order by relavance
		arsort($word_relavance);
		reset($word_relavance);

		// Use the most interesting words (use all if we have less than the relevant number)
		$interesting_words = array();

		for ($i = 0; $i < $this->config['use_relevant']; $i++)
		{
			if ($word = each($word_relavance))
			{
				// Use the word if its probability is relevant enough
				if (abs(0.5 - $word_probability[$word['key']]) > $this->config['min_deviation'])
				{
					// Words that occurred more than once, count more than once
					for ($j = 0; $j < $word_occurance[$word['key']]; $j++)
					{
						$interesting_words[] = $word_probability[$word['key']];
					}
				}
			}
			else
			{
				// We have less words than the relevant amount
				break;
			}
		}

		// Calculate the spammines of the text (Mr. Robinson)

		// Default to 1 for first multiplication
		$hamminess  = 1;
		$spamminess = 1;

		// Consider all interesting words
		foreach ($interesting_words as $probability)
		{
			$hamminess  *= (1.0 - $probability);
			$spamminess *= $probability;
		}

		// If no word was good for calculation, we really don't know how to
		// rate this text so we assume a ham and spam probability of 0.5
		if ($hamminess == 1 AND $spamminess == 1)
		{
			$hamminess  = 0.5;
			$spamminess = 0.5;
			$count = 1;
		}
		else
		{
			// Get number interesting words
			$count = count($interesting_words);
		}

		// Calculate the combined rating

		// The actual hamminess and spamminess
		$hamminess  = 1 - pow($hamminess,  (1 / $count));
		$spamminess = 1 - pow($spamminess, (1 / $count));

		// Calculate the combined indicator
		$probability = ($hamminess - $spamminess) / ($hamminess + $spamminess);

		// We want a value between 0 and 1, not between -1 and +1
		$probability = (1 + $probability) / 2;

		// Return as a predefined constant
		if (($use_classify != NULL AND $use_classify == 'const') OR $this->config['use_classify'] == 'const')
		{
			$use_classify = 'const';
			
			if ($probability <= $this->config['classify'][$use_classify]['ham'])
			{
				return B8::HAM;
			}
			else if ($probability >= $this->config['classify'][$use_classify]['spam'])
			{
				return B8::SPAM;
			}
			else
			{
				return B8::UNSURE;
			}
		}

		// Return as a float
		return $probability;
	}

	/**
	 * Calculate probability of word being spam
	 *
	 * @param   array   'ham' and 'spam' counts for word
	 * @param   array   total 'ham' and 'spam' counts of learned texts
	 * @return  float
	 */
	protected function calculate_probability(array $word, array $total)
	{
		// Calculate basic probability (Mr. Graham)
		// Consider the number of ham and spam texts learnt instead of the
		// number of times the word occurred to calculate a relative spamminess
		// because we count words appearing multiple times not just once but
		// as often as they appear in the learned texts

		if ($total[self::HAM] > 0)
		{
			$word[self::HAM] = $word[self::HAM] / $total[self::HAM];
		}

		if ($total[self::SPAM] > 0)
		{
			$word[self::SPAM] = $word[self::SPAM] / $total[self::SPAM];
		}

		$probability = $word[self::SPAM] / ($word[self::HAM] + $word[self::SPAM]);

		// Calculate better probability (Mr. Robinson)
		$total = $total[self::HAM] + $total[self::SPAM];

		return (($this->config['rob_s'] * $this->config['rob_x']) + ($total * $probability)) / ($this->config['rob_s'] + $total);
	}

	/**
	 * Check whether a category is valid.
	 *
	 * @param   string  text to learn
	 * @param   string  classification category
	 * @return  bool
	 */
	protected function valid_category($category)
	{
		return ($category == self::HAM OR $category == self::SPAM);
	}

	/**
	 * Learn text
	 *
	 * @param   string  text to learn
	 * @param   string  classification category (B8::HAM or B8::SPAM)
	 * @return  bool
	 */
	public function learn($text, $category)
	{
		return $this->process_text($text, $category, self::LEARN);
	}

	/**
	 * Unlearn text
	 *
	 * @param   string  text to unlearn
	 * @param   string  classification category (B8::HAM or B8::SPAM)
	 * @return  bool
	 */
	public function unlearn($text, $category)
	{
		return $this->process_text($text, $category, self::UNLEARN);
	}

	/**
	 * Process text
	 *
	 * @param   string  text to process
	 * @param   string  classification category (B8::HAM or B8::SPAM)
	 * @param   string  action (B8::LEARN or B8::UNLEARN)
	 * @return  bool
	 */
	protected function process_text($text, $category, $action)
	{
		// Don't continue if category is invalid
		if ( ! $this->valid_category($category))
		{
			return FALSE;
		}

		// Get words from text
		$words = $this->lexer->get_words($text);

		return $this->storage->process_text($words, $category, $action);
	}
}
