<?php

/**
 * Export all learners for a course.
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

$page->layout = false;
header ('Cache-control: private');
header ('Content-Type: text/plain');
header ('Content-Disposition: attachment; filename=all-learners.csv');

echo "\"ID\",\"Name\",\"Email\",\"Company\",\"Joined\",\"Progress\",\"Score\",\"Passed\"\n";

foreach ($learners as $learner) {
	$row = array (
		$learner->id,
		str_replace ('"', '""', $learner->name),
		$learner->email,
		str_replace ('"', '""', $learner->company),
		$learner->joined,
		$learner->progress,
		courses\Filter::learner_score ($learner->score),
		courses\Filter::learner_passed ($learner->passed)
	);

	$sep = '';
	foreach ((array) $row as $k => $v) {
		$v = str_replace ('"', '""', $v);
		if (strpos ($v, ',') !== false) {
			$v = '"' . $v . '"';
		}
		$v = str_replace (array ("\n", "\r"), array ('\\n', '\\r'), $v);
		echo $sep . $v;
		$sep = ',';
	}
	echo "\n";
}
