<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
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

			// Words to ignore as they are so common that they can always be considered neutral
			'ignore_words' => array
			(
				'a',     'about', 'after',   'all',    'also',  'an',   'and',   'any',   'are',  'as', 
				'at',    'be',    'because', 'but',    'by',    'can',  'come',  'could', 'did',  'do', 
				'for',   'from',  'get',     'give',   'go',    'had',  'have',  'he',    'her',  'him', 
				'his',   'how',   'i',       'if',     'in',    'into', 'it',    'its',   'just', 'knew', 
				'know',  'like',  'look',    'make',   'many',  'me',   'more',  'most',  'my',   'new', 
				'no',    'not',   'now',     'of',     'on',    'only', 'or',    'other', 'our',  'out', 
				'over',  'see',   'she',     'should', 'so',    'some', 'take',  'than',  'that', 'the', 
				'their', 'them',  'then',    'there',  'these', 'they', 'think', 'this',  'to',   'up', 
				'us',    'use',   'want',    'was',    'way',   'we',   'well',  'were',  'what', 'when', 
				'where', 'which', 'who',     'why',    'will',  'with', 'would', 'you',   'your',
			),
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
	 * value is, the more will the filter fail on texts including passages from
	 * books, etc.
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
