<?php

/**
 * Updates score and passed values for a learner.
 */

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

$c = new courses\Course ($_POST['id']);
if ($c->error) {
	echo View::render ('courses/admin/error', $c);
	return;
}

$u = new User ($_POST['learner']);
if ($u->error) {
	echo View::render ('courses/admin/error', $u);
	return;
}

if (! is_numeric ($_POST['score'])) {
	$this->add_notification ('Invalid score value. Please use a number.');
	$this->redirect ('/courses/course/learner?id=' . $_POST['id'] . '&learner=' . $_POST['learner']);
}

if (! is_numeric ($_POST['passed'])) {
	$this->add_notification ('Invalid passed value. Please choose a value from the dropdown.');
	$this->redirect ('/courses/course/learner?id=' . $_POST['id'] . '&learner=' . $_POST['learner']);
}

courses\Learner::update_score ($u->id, $_POST['score']);
courses\Learner::update_passed ($u->id, $_POST['passed']);

$this->add_notification ('Learner updated.');
$this->redirect ('/courses/course/learner?id=' . $_POST['id'] . '&learner=' . $_POST['learner']);
