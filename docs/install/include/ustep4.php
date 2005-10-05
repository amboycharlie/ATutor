<?php
/************************************************************************/
/* ATutor																*/
/************************************************************************/
/* Copyright (c) 2002-2004 by Greg Gay, Joel Kronenberg, Heidi Hazelton	*/
/* http://atutor.ca														*/
/*																		*/
/* This program is free software. You can redistribute it and/or		*/
/* modify it under the terms of the GNU General Public License			*/
/* as published by the Free Software Foundation.						*/
/************************************************************************/
// $Id$

ignore_user_abort(true); 
@set_time_limit(0); 

if (!defined('AT_INCLUDE_PATH')) { exit; }

if (isset($_POST['submit'])) {
	if (!isset($errors)) {
		unset($_POST['submit']);
		unset($action);
		store_steps($step);
		$step++;
		return;
	}
}

print_progress($step);

/* try copying the content over from the old dir to the new one */
require('../include/lib/filemanager.inc.php');

$content_dir = urldecode(trim($_POST['step4']['content_dir']));
$_POST['step4']['copy_from'] = urldecode(trim($_POST['step4']['copy_from'])) . DIRECTORY_SEPARATOR;

//copy if copy_from is not empty

if ($_POST['step4']['copy_from'] && ($_POST['step4']['copy_from'] != DIRECTORY_SEPARATOR)) {
	if (is_dir($_POST['step4']['copy_from'])) {

		$courses = scandir($_POST['step4']['copy_from']);

		foreach ($courses as $course) {
			if (is_numeric($course)) {
				copys($_POST['step4']['copy_from'].$course, $content_dir.$course);
				if (is_dir($content_dir.$course)) {					
					$progress[] = 'Course content directory <b>'.$course.'</b> copied successfully.';
				} else {
					$errors[] = 'Course content directory <b>'.$course.'</b> <strong>NOT</strong> copied.';
				}
			} 
		}
	}

	if (is_dir($_POST['step4']['copy_from'].'chat/')) {
		$courses = scandir($_POST['step4']['copy_from'].'chat/');

		foreach ($courses as $course) {
			if (is_numeric($course)) {
				copys($_POST['step4']['copy_from'].'chat/'.$course, $content_dir.'chat/'.$course);
			} 
		}
		$progress[] = 'Course chat directories copied successfully.';
	}

	if (is_dir($_POST['step4']['copy_from'].'backups/')) {
		$courses = scandir($_POST['step4']['copy_from'].'backups/');

		foreach ($courses as $course) {
			if (is_numeric($course)) {
				copys($_POST['step4']['copy_from'].'backups/'.$course, $content_dir.'backups/'.$course);
			} 
		}
		$progress[] = 'Course backup directories copied successfully.';
	}
} else {
	$progress[] = 'Using existing content directory <strong>'.$content_dir.'</strong>.';
}

/* deal with the extra modules: */
/* for each module in the modules table check if that module still exists in the mod directory. */
/* if that module does not exist then check the old directory and prompt to have it copied */
/* or delete it from the modules table. or maybe disable it instead? */


echo '<br />';
if (isset($progress)) {
	print_feedback($progress);
}
if (isset($errors)) {
	print_errors($errors);
}

if ($_POST['step1']['cache_dir'] != '') {
	define('CACHE_DIR', urldecode($_POST['step1']['cache_dir']));
	define('CACHE_ON', 1);
	require('../include/phpCache/phpCache.inc.php');
	cache_gc(NULL, 1, true);
}

?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="form">
<input type="hidden" name="step" value="<?php echo $step;?>" />
<?php print_hidden($step); ?>

<br /><br /><p align="center"><input type="submit" class="button" value=" Next &raquo;" name="submit" /></p>
</form>