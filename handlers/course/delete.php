<?php

$this->require_acl ('admin', 'courses');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	$this->redirect ('/courses/admin');
}

$page->layout = 'admin';

// fetch the course
$c = new courses\Course ($_POST['id']);
if ($c->error) {
	echo View::render ('courses/admin/error', $c);
	return;
}

if (! $c->delete ()) {
	echo View::render ('courses/admin/error', $c);
	return;
}

$this->add_notification (__ ('Course deleted.'));
$this->redirect ('/courses/admin');

?>