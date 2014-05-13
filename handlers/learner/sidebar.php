<?php

if (! User::is_valid ()) {
	return;
}

$courses = courses\Learner::courses ();

echo View::render (
	'courses/learner/sidebar',
	array (
		'courses' => $courses
	)
);

?>