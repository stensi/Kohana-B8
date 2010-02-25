<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Abstract degenerator class to standardise degenerator api.
 *
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
abstract class B8_Degenerator {

	// Degenerates
	public $degenerates;

	/**
	 * Creates and returns a new Degenerator.
	 *
	 * @param   string  configuration group
	 * @return  Degenerator
	 */
	public static function factory($degenerator = 'default')
	{
		// Set class name
		$class = 'B8_Degenerator_'.ucfirst($degenerator);

		return new $class();
	}

	/**
	 * Builds an array of "degenerated" words from an array of words.
	 *
	 * @access  public
	 * @param   array   words to degenerate
	 * @return  array   array of degenerated words for each word
	 */
	abstract public function degenerate(array $words);

}
