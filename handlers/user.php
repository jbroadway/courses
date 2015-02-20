<?php

$this->require_acl ('admin', 'user', 'courses');

if (! $this->internal) return;

$courses = courses\Learner::courses ($this->data['user']);

echo View::render ('courses/user', array (
		'courses' => $courses
));
