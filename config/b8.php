<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
	/**
	 * Which type of classify return value to use.
	 */
	'use_classify' => 'default',
	
	/**
	 * Settings for classify return value.
	 */
	'classify' => array
	(
		// Return a float between 0 (ham) and 1 (spam)
		'default' => 'float',
		
		// Return a predefined constant based on lower (ham) and upper (spam) ranges
		'const' => array
		(
			// Text classified as this value or lower are B8::HAM
			'ham' => 0.2,
					
			// Text classified as this value or higher are B8::SPAM
			'spam' => 0.8,
			
			// Text classified between ham and spam are B8::UNSURE
		),
	),

	/**
	 * Which lexer to use. There's just one lexer at the moment (default).
	 */
	'use_lexer' => 'default',

	/**
	 * Settings for each lexer.
	 */
	'lexer' => array
	(
		// Default lexer
		'default' => array
		(
			// Minimum length for a word
			'min_size' => 3,

			// Maximum length for a word
			'max_size' => 30,

			// Whether pure numbers can be a word
			'allow_numbers' => FALSE,
		),
	),

	/**
	 * Which storage to use. There's just one storage at the moment (default).
	 */
	'use_storage' => 'default',

	/**
	 * Settings for each storage.
	 */
	'storage' => array
	(
		// Default storage
		'default' => array
		(
			// Which degenerator to use. There's just one degenerator at the moment (default).
			'use_degenerator' => 'default',

			// Database configuration group to use from the Database module
			'database' => 'default',

			// Database table to store words
			'word_table' => 'b8_words',

			// Database table to store category text counts
			'category_table' => 'b8_categories',
		),
	),

	/**
	 * DON'T CHANGE THE BELOW VALUES UNLESS YOU KNOW WHAT YOU ARE DOING!
	 * IT COULD RESULT IN POOR PERFORMANCE!
	 */

	/**
	 * The number of words to use when classifying longer texts. The higher this
	 * value is, the more likely the filter will fail on texts including passages
	 * from books, etc.
	 *
	 * Default: 15
	 */
	'use_relevant' => 15,

	/**
	 * This sets the minimun deviation that a word's rating has to have from 0.5.
	 * A word with a rating closer to 0.5 won't be used for the calculation.
	 *
	 * Default: 0.2
	 */
	'min_deviation' => 0.2,

	/**
	 * This is Gary Robinson's "x" constant. A completely unknown word will be
	 * rated with rob_x. 0.5 for rob_x seems quite reasonable, as we can't say if
	 * a word that also can't be rated by degeneration is good or bad.
	 *
	 * Default: 0.5
	 */
	'rob_x' => 0.5,

	/**
	 * This is Gary Robinson's "s" constant. This is essentially the probability
	 * that the rob_x value is the correct one for an unknown word. It will also
	 * shift the probability of rarely seen words towards rob_x.
	 *
	 * Default: 0.3
	 */
	'rob_s' => 0.3,
);
