<?php

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

$c = new courses\Course ($_GET['id']);
if ($c->error) {
	echo View::render ('courses/admin/error', $c);
	return;
}

$page->title = $c->title . ' - ' . __ ('Pages');

$page->add_style ('/apps/courses/css/admin.css');
$page->add_script ('/apps/courses/js/admin.js');
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');

echo View::render ('courses/course/manage', array (
	'course' => $c->id,
	'course_title' => $c->title,
	'pages' => $c->pages ()
));

?>