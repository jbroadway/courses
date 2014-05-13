<?php

$this->require_login ();

$courses = courses\Learner::courses ();

echo View::render (
	'courses/learner/courses',
	array (
		'courses' => $courses
	)
);

?>