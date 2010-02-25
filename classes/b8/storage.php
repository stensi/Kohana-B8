<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Abstract storage class to standardise storage api.
 *
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
abstract class B8_Storage {

	/**
	 * Creates and returns a new Lexer.
	 *
	 * @param   string  configuration group
	 * @param   array   custom configuration
	 * @return  Lexer
	 */
	public static function factory($storage = 'default', array $config = array())
	{
		// Set class name
		$class = 'B8_Storage_'.ucfirst($storage);

		return new $class($config);
	}

	/**
	 * Get total counts of learned ham or spam texts.
	 *
	 * @param   string  classification category
	 * @return  int     total texts learned by category
	 */
	abstract public function get_total($category);

	/**
	 * Process words
	 *
	 * @param   array   words to process
	 * @return  bool
	 */
	abstract public function process_words(array $words);

	/**
	 * Process text
	 *
	 * @param   string  words from text to process
	 * @param   string  classification category (B8::HAM or B8::SPAM)
	 * @param   string  action (B8::LEARN or B8::UNLEARN)
	 * @return  bool
	 */
	abstract public function process_text($words, $category, $action);

}
