<?php

$this->require_acl ('admin', 'courses');

if (! isset ($_GET['category']) || empty ($_GET['category'])) {
	$this->redirect ('/courses/admin');
}

$cat = new courses\Category;
$cat->owner = 0;
$cat->title = $_GET['category'];
$cat->sorting = $cat->next ('sorting');

if (! $cat->put ()) {
	$this->add_notification ('An error occurred.');
} else {
	$this->add_notification ('Category added.');
}

$this->redirect ('/courses/admin');

?>