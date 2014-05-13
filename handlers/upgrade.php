<?php

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

if ($this->installed ('courses', $appconf['Admin']['version']) === true) {
	$page->title = 'Already up-to-date';
	echo '<p><a href="/">Home</a></p>';
	return;
}

$page->title = 'Upgrading app: Courses';

echo '<p>Done.</p>';

$this->mark_installed ('courses', $appconf['Admin']['version']);

?>