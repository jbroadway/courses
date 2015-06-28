<?php

/**
 * Displays a list of available courses, or if the user is logged in,
 * a list of the courses they are taking and/or instructing. Passes
 * course content requests to `courses/course`.
 */

if (count ($this->params) > 0) {
	echo $this->run ('courses/course/' . join ('/', $this->params));
	return;
}

$page->id = 'courses';
$page->title = __ ($appconf['Courses']['public_name']);
$page->layout = $appconf['Courses']['layout'];

$this->run ('admin/util/minimal-grid');
$page->add_style ('/apps/courses/css/list.css');

$discount = 0;
$skip = array ();

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

	$discount = courses\App::discount ();

	$skip = array ();
	foreach ($courses as $course) {
		$skip[] = $course->id;
	}
	foreach ($instructing as $course) {
		$skip[] = $course->id;
	}
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
	if (in_array ($courses[$k]->id, $skip)) {
		continue;
	}

	$courses[$k]->discount = $discount;
	$c = new courses\Course ((array) $courses[$k]);
	$courses[$k]->discount_price = $c->discount_price ($discount);
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