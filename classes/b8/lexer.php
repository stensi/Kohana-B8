<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Abstract lexer class to standardise lexer api.
 *
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
abstract class B8_Lexer {

	/**
	 * Creates and returns a new Lexer.
	 *
	 * @param   string  configuration group
	 * @param   array   custom configuration
	 * @return  Lexer
	 */
	public static function factory($lexer = 'default', array $config = array())
	{
		// Set class name
		$class = 'B8_Lexer_'.ucfirst($lexer);

		return new $class($config);
	}

	/**
	 * Get words from text.
	 *
	 * @param   string  text to get words from
	 * @return  array
	 */
	abstract public function get_words($text);

	/**
	 * Check whether a word is valid.
	 *
	 * @param   string  word to validate
	 * @return  bool
	 */
	abstract protected function valid_word($word);
}
