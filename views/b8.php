<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>B8 module</title>
</head>
<body>
	<h1>B8 module</h1>

	<?php echo Form::open(); ?> 

	<div>
		<?php echo $message; ?>
	</div>
	<div>
		<?php echo Form::label('text', 'Text'); ?><br />
		<?php echo Form::textarea('text', Arr::get($_POST, 'text'), array('cols' => 50, 'rows' => 16)); ?> 
	</div>
	<div>
		<?php echo Form::button('action', 'Classify', array('type' => 'submit', 'value' => 'Classify')); ?>
	</div>
	<hr />
	<div>
		<?php echo Form::button('action', 'Learn as HAM', array('type' => 'submit', 'value' => 'Learn as HAM')); ?> 
		<?php echo Form::button('action', 'Unlearn as HAM', array('type' => 'submit', 'value' => 'Unlearn as HAM')); ?>
	</div>
	<hr />
	<div>
		<?php echo Form::button('action', 'Learn as SPAM', array('type' => 'submit', 'value' => 'Learn as SPAM')); ?>
		<?php echo Form::button('action', 'Unlearn as SPAM', array('type' => 'submit', 'value' => 'Unlearn as SPAM')); ?> 
	</div>

	<?php echo Form::close(); ?> 
</body>
</html>