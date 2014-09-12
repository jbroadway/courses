<?php

$q = courses\Course::query ();

if (isset ($data['category']) && ! empty ($data['category'])) {
	$q->where ('category', $data['category']);
}

if (isset ($data['descriptions']) && $data['descriptions'] === 'yes') {
	$courses = $q->where ('status', 2)
		->where ('availability > 1')
		->order ('sorting', 'asc')
		->fetch_orig ();

	echo $tpl->render ('courses/list', array ('courses' => $courses, 'desc' => true));
} else {
	$courses = $q->where ('status', 2)
		->where ('availability > 1')
		->order ('sorting', 'asc')
		->fetch_assoc ('id', 'title');

	echo $tpl->render ('courses/list', array ('courses' => $courses, 'desc' => false));
}

?>