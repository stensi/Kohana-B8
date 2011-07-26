<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Default lexer class to split text into words.
 *
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
class B8_Lexer_Default extends Lexer {

	// The regular expressions we use to split the text to words
	public static $regex = array(
		'ip'        => '/([A-Za-z0-9\_\-\.]+)/',
		'raw_split' => '/[\s,\.\/"\:;\|<>\-_\[\]{}\+=\)\(\*\&\^%]+/',
		'html'      => '/(<.+?>)/',
		'tag_name'  => '/(.+?)\s/',
		'numbers'   => '/^[0-9]+$/'
	);

	// Configuration
	protected $config = array();

	/**
	 * Creates a new Lexer_Default object.
	 *
	 * @param   array   configuration
	 * @return  void
	 */
	public function __construct(array $config = array())
	{
		// Load the lexer default config file
		$this->config = Kohana::$config->load('b8.lexer.default');

		// Overwrite with custom config settings
		foreach ($config as $key => $value)
		{
			$this->config[$key] = $value;
		}
	}

	/**
	 * Get words from text.
	 *
	 * @param   string  text to get words from
	 * @return  array
	 */
	public function get_words($text)
	{
		$words = array();

		// Don't continue if string is invalid
		if ( ! is_string($text) OR empty($text))
		{
			// The given parameter is not a string or is empty
			return $words;
		}

		// Get internet and IP addresses
		preg_match_all(self::$regex['ip'], $text, $raw_words);

		// Process them
		foreach($raw_words[1] as $word)
		{
			// Check for . and that the word is valid
			if (strpos($word, '.') !== FALSE AND $this->valid_word($word))
			{
				if ( ! isset($words[$word]))
				{
					$words[$word] = 1;
				}
				else
				{
					$words[$word]++;
				}

				// Delete the words we've processed from text
				$text = str_replace($word, '', $text);

				// Process each part of the URLs
				$url_parts = preg_split(self::$regex['raw_split'], $word);

				foreach($url_parts as $word)
				{
					// Include word if valid
					if ($this->valid_word($word))
					{
						if ( ! isset($words[$word]))
						{
							$words[$word] = 1;
						}
						else
						{
							$words[$word]++;
						}
					}
				}
			}
		}

		// Split the remaining text
		$raw_words = preg_split(self::$regex['raw_split'], $text);

		foreach($raw_words as $word)
		{
			if ($this->valid_word($word))
			{
				if ( ! isset($words[$word]))
				{
					$words[$word] = 1;
				}
				else
				{
					$words[$word]++;
				}
			}
		}

		// Get HTML
		preg_match_all(self::$regex['html'], $text, $raw_words);

		foreach($raw_words[1] as $word)
		{
			if ($this->valid_word($word))
			{
				// If the tag has parameters, just use the tag
				if (strpos($word, ' ') !== FALSE)
				{
					preg_match(self::$regex['tag_name'], $word, $tmp);
					$word = "{$tmp[1]}...>";
				}

				if ( ! isset($words[$word]))
				{
					$words[$word] = 1;
				}
				else
				{
					$words[$word]++;
				}
			}
		}

		// Return a list of all found words
		return $words;
	}

	/**
	 * Check whether a word is valid.
	 *
	 * @param   string  word to validate
	 * @return  bool
	 */
	protected function valid_word($word)
	{
		// Check for a proper length
		if (strlen($word) < $this->config['min_size'] OR strlen($word) > $this->config['max_size'])
		{
			return FALSE;
		}

		// Check if it is a number and whether they're allowed
		if ( ! $this->config['allow_numbers'])
		{
			if (preg_match(self::$regex['numbers'], $word))
			{
				return FALSE;
			}
		}

		// Word is valid
		return TRUE;
	}
}
