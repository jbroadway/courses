<?php

$page->layout = 'admin';

$this->require_acl ('admin', 'courses');

$page->title = __ ('Courses');

// fetch sorted categories
$categories = courses\Category::sorted ();

// fetch and sort courses by category
$courses = courses\Course::categories ();
foreach (array_keys ($categories) as $k) {
	$categories[$k] = (object) array (
		'id' => $k,
		'title' => $categories[$k],
		'courses' => array ()
	);
}
foreach (array_keys ($courses) as $k) {
	$categories[$courses[$k]->category]->courses[] = $courses[$k];
}

$page->add_style ('/apps/courses/css/admin.css');
$page->add_script ('/apps/courses/js/admin.js');

echo $tpl->render ('courses/admin', array (
	'categories' => $categories
));

?>