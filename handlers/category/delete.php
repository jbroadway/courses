<?php

$this->require_acl ('admin', 'courses');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	$this->redirect ('/courses/admin');
}

if (! isset ($_POST['id']) || ! is_numeric ($_POST['id'])) {
	$this->redirect ('/courses/admin');
}

$cat = new courses\Category ($_POST['id']);

if (! $cat->remove ()) {
	$this->add_notification ('An error occurred.');
} else {
	$this->add_notification ('Category deleted.');
}

$this->redirect ('/courses/admin');

?>