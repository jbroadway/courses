<?php

if (count ($this->params) > 0) {
	echo $this->run ('courses/course/' . join ('/', $this->params));
	return;
}

$page->id = 'courses';
$page->title = __ ($appconf['Courses']['public_name']);
$page->layout = $appconf['Courses']['layout'];

$this->run ('admin/util/minimal-grid');
$page->add_style ('/apps/courses/css/list.css');

if (User::is_valid ()) {
	$courses = courses\Learner::courses ();

	$instructing = courses\Course::instructing ();

	echo $tpl->render (
		'courses/mycourses',
		array (
			'courses' => $courses,
			'instructing' => $instructing
		)
	);
}

// fetch sorted categories
$categories = courses\Category::sorted ();

// fetch and sort courses by category (published, any owner)
$courses = courses\Course::categories (false, true);
if (count ($courses) === 0) {
	printf ('<p>%s</p>', __ ('No courses available at this time.'));
	return;
}

foreach (array_keys ($categories) as $k) {
	$categories[$k] = (object) array (
		'id' => $k,
		'title' => $categories[$k],
		'courses' => array (),
		'course_count' => 0
	);
}
foreach (array_keys ($courses) as $k) {
	$categories[$courses[$k]->category]->courses[] = $courses[$k];
	$categories[$courses[$k]->category]->course_count++;
}

echo $tpl->render (
	'courses/index',
	array (
		'categories' => $categories
	)
);

?>