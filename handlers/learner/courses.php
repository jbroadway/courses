<?php

$this->require_login ();

$courses = courses\Learner::courses ();

$this->run ('admin/util/minimal-grid');
$page->add_style ('/apps/courses/css/list.css');

echo View::render (
	'courses/learner/courses',
	array (
		'courses' => $courses
	)
);

?>