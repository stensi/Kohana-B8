<?php defined('SYSPATH') or die('No direct script access.');

/**
 * B8 example.
 *
 * @package B8
 *
 * @author  Simon Stenhouse  http://stensi.com/   (port of PHP5 b8 to a Kohana 3 module)
 * @author  Tobias Leupold   http://nasauber.de/  (original author)
 *
 * @license http://www.gnu.org/licenses/lgpl.txt
 */
class Controller_B8 extends Controller_Template {

	public function action_index()
	{
		$this->template = View::factory('b8')
			->bind('message', $message);

		$B8 = B8::factory();

		// Check for action
		if (isset($_POST['action']))
		{
			// Check for text
			if (empty($_POST['text']))
			{
				$message = '<p style="color:red;"><b>Please enter some text</b></p>';
			}
			else
			{
				// Process action
				switch ($_POST['action'])
				{
					case 'Classify':
						$probability = $B8->classify($_POST['text']);

						$message = '<p>Classification: '.$this->format_probability($probability).'</p>';

						break;

					case 'Learn as HAM':
						$probability_before = $B8->classify($_POST['text']);

						$B8->learn($_POST['text'], B8::HAM);

						$probability_after  = $B8->classify($_POST['text']);

						$message = '<p>Classification before learning as HAM: '.$this->format_probability($probability_before).'</p>';
						$message .= '<p>Classification after learning as HAM: '.$this->format_probability($probability_after).'</p>';

						break;

					case 'Learn as SPAM':
						$probability_before = $B8->classify($_POST['text']);

						$B8->learn($_POST['text'], B8::SPAM);

						$probability_after  = $B8->classify($_POST['text']);

						$message = '<p>Classification before learning as SPAM: '.$this->format_probability($probability_before).'</p>';
						$message .= '<p>Classification after learning as SPAM: '.$this->format_probability($probability_after).'</p>';

						break;

					case 'Unlearn as HAM':
						$probability_before = $B8->classify($_POST['text']);

						$B8->unlearn($_POST['text'], B8::HAM);

						$probability_after  = $B8->classify($_POST['text']);

						$message = '<p>Classification before unlearning as HAM: '.$this->format_probability($probability_before).'</p>';
						$message .= '<p>Classification after unlearning as HAM: '.$this->format_probability($probability_after).'</p>';

						break;

					case 'Unlearn as SPAM':
						$probability_before = $B8->classify($_POST['text']);

						$B8->unlearn($_POST['text'], B8::SPAM);

						$probability_after  = $B8->classify($_POST['text']);

						$message = '<p>Classification before unlearning as SPAM: '.$this->format_probability($probability_before).'</p>';
						$message .= '<p>Classification after unlearning as SPAM: '.$this->format_probability($probability_after).'</p>';

						break;
				}
			}
		}
	}

	protected function format_probability($probability)
	{
		$red   = floor(255 * $probability);
		$green = floor(255 * (1 - $probability));

		return '<span style="color:rgb('.$red.', '.$green.', 0)"><b>'.sprintf("%5f", $probability).'</b></span>';
	}
}
