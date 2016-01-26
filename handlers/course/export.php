<?php

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
}

$page->layout = false;
header ('Cache-control: private');
header ('Content-Type: text/plain');
header ('Content-Disposition: attachment; filename=all-learners.csv');

echo "\"ID\",\"Name\",\"Email\",\"Progress\"\n";

foreach ($learners as $learner) {
	printf (
		"\"%s\",\"%s\",\"%s\",\"%s\"\n",
		$learner->id,
		str_replace ('"', '""', $learner->name),
		$learner->email,
		$learner->progress
	);
}
