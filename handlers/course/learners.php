<?php

/**
 * Provides instructors with a list of learners.
 */

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

$c = new courses\Course ($_GET['id']);
if ($c->error) {
	echo View::render ('courses/admin/error', $c);
	return;
}

$learners = $c->learners ();

$progress = courses\Data::for_course ($c->id);
foreach ($learners as $k => $learner) {
	$learners[$k]->progress = isset ($progress[$learner->id])
		? ceil ($progress[$learner->id])
		: 0;

	$learners[$k]->joined = strip_tags (I18n::short_date_year ($learner->ts));
}

$page->title = $c->title . ' - ' . __ ('Learners') . ' (<span id="learner-count">' . count ($learners) . '</span>)';

$page->add_style ('/apps/courses/css/admin.css');
$page->add_script ('/apps/courses/js/handlebars.js');
$page->add_script ('/apps/courses/js/admin.js');
$page->add_script (I18n::export (
	'Learner removed.',
	'Learner added.',
	'Are you sure you want to remove this learner from the course?',
	'Incomplete',
	'Complete',
	'Yes',
	'No'
));

echo View::render ('courses/course/learners', array (
	'course' => $c->id,
	'course_title' => $c->title,
	'learners' => $learners
));
