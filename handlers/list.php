<?php

$q = courses\Course::query ();

if (isset ($data['category']) && ! empty ($data['category'])) {
	$q->where ('category', $data['category']);
}

$courses = $q->where ('status', 2)
	->where ('availability > 1')
	->order ('sorting', 'asc')
	->fetch_assoc ('id', 'title');

echo $tpl->render ('courses/list', array ('courses' => $courses));

?>