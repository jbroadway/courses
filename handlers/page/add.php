<?php

$this->require_acl ('admin', 'courses');

if (! isset ($_GET['course']) || empty ($_GET['course'])) {
	$this->redirect ('/courses/admin');
}

if (! isset ($_GET['page']) || empty ($_GET['page'])) {
	$this->redirect ('/courses/admin');
}

$pg = new courses\Page;
$pg->title = $_GET['page'];
$pg->course = $_GET['course'];
$pg->sorting = $pg->next ('sorting');

if (! $pg->put ()) {
	$this->add_notification ('An error occurred.');
} else {
	$this->add_notification ('Page added.');
}

$this->redirect ('/courses/course/content?id=' . $_GET['course'] . '&page=' . $pg->id);

?>