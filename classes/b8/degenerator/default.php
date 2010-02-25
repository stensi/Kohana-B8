<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Default degenerator class to form degenerated words of other words.
 *
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
class B8_Degenerator_Default extends Degenerator {

	// Degenerates
	public $degenerates = array();

	/**
	 * Builds an array of "degenerated" words from an array of words.
	 *
	 * @access  public
	 * @param   array   words to degenerate
	 * @return  array   array of degenerated words for each word
	 */
	public function degenerate(array $words)
	{
		$degenerates = array();

		foreach ($words as $word)
		{
			$degenerates[$word] = $this->_degenerate_word($word);
		}

		return $degenerates;
	}

	/**
	 * Builds "degenerated" words if the word does not exist in the database.
	 *
	 * @access  protected
	 * @param   string  word to degenerate
	 * @return  array   array of degenerated words of the word
	 */
	protected function _degenerate_word($word)
	{
		// Check if word has been processed already
		if (isset($this->degenerates[$word]) === TRUE)
		{
			return $this->degenerates[$word];
		}

		$degenerates = array();

		// Add word with upper, lower and ucfirst casing
		$degenerates[] = strtolower($word);
		$degenerates[] = strtoupper($word);
		$degenerates[] = ucfirst($word);

		// Temporarily remove ! or ? or . from end of word for inflections
		$w = preg_replace('/([!?\.])+$/', '', $word);

		// Add inflections
		$singular = Inflector::singular($w);
		$plural   = Inflector::plural($w);

		$degenerates[] = $singular;
		$degenerates[] = strtoupper($singular);
		$degenerates[] = ucfirst($singular);
		$degenerates[] = $plural;
		$degenerates[] = strtoupper($plural);
		$degenerates[] = ucfirst($plural);

		// Remove duplicates
		$degenerates = array_unique($degenerates);

		// Degenerate each casing versions
		foreach($degenerates as $degenerate)
		{
			// Check for ! or ? at end of word
			if(preg_match('/[!?]$/', $degenerate))
			{
				// Reduce to single ! or ? at end of word, if multiple were found
				if (preg_match('/[!?]{2,}$/', $degenerate) > 0)
				{
					$degenerates[] = preg_replace('/([!?])+$/', '$1', $degenerate);
				}

				// Remove ! or ? from end of word
				$degenerates[] = preg_replace('/([!?])+$/', '', $degenerate);
			}
			else
			{
				// Add ! and ? to end of word
				$degenerates[] = $degenerate.'!';
				$degenerates[] = $degenerate.'?';
			}

			// Remove . from end of word
			if (preg_match('/[\.]$/', $degenerate))
			{
				while(preg_match('/[\.]$/', $degenerate))
				{
					$degenerates[] = substr($degenerate, 0, strlen($degenerate) - 1);
				}
			}
			else
			{
				// Add . to end of word
				$degenerates[] = $degenerate.'.';
			}
		}

		// Remove duplicates
		$degenerates = array_unique($degenerates);

		// Remove degenerate word that matches the original word 
		if (in_array($word, $degenerates))
		{
			foreach ($degenerates as $key => $degenerate)
			{
				if ($degenerate == $word)
				{
					unset($degenerates[$key]);

					break;
				}
			}
		}

		// Store the list of degenerates for the word
		$this->degenerates[$word] = $degenerates;

		return $degenerates;
	}
}
