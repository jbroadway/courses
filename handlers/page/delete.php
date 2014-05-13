<?php

$this->require_acl ('admin', 'courses');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	$this->redirect ('/courses/admin');
}

if (! isset ($_POST['id']) || ! is_numeric ($_POST['id'])) {
	$this->redirect ('/courses/admin');
}

if (! isset ($_POST['course']) || ! is_numeric ($_POST['course'])) {
	$this->redirect ('/courses/admin');
}

$pg = new courses\Page ($_POST['id']);

if ($pg->error) {
	$this->redirect ('/courses/admin');
}

if ($pg->course !== $_POST['course']) {
	$this->redirect ('/courses/admin');
}

if (! $pg->remove ()) {
	$this->add_notification ('An error occurred.');
} else {
	$this->add_notification ('Page deleted.');
}

$this->redirect ('/courses/course/manage?id=' . $_POST['course']);

?>