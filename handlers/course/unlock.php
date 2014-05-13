<?php

/**
 * Unlock the page or course for editing by others,
 * and redirect to the appropriate screen.
 */

$this->require_acl ('admin', 'courses');

if (isset ($_GET['page'])) {
	// Unlock a page and go to pages list
	$lock = new Lock ('courses_page', $_GET['id'] . '/' . $_GET['page']);
	$lock->remove ();
	$this->redirect ('/courses/course/manage?id=' . $_GET['id']);
} else {
	// Unlock a course and go to courses list
	$lock = new Lock ('courses_course', $_GET['id']);
	$lock->remove ();
	$this->redirect ('/courses/admin');
}

?>