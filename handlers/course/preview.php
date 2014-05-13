<?php

$this->require_acl ('admin', 'courses');

$page->layout = false;

$c = new courses\Course ($_GET['id']);
if ($c->error) {
	echo View::render ('courses/admin/error', $c);
	return;
}

$p = new courses\Page ($_GET['page']);
if ($p->error) {
	echo View::render ('courses/admin/error', $p);
	return;
}

$page->add_script ('/apps/courses/js/preview.js');

printf ('<h1>%s</h1>', $p->title);

$items = $p->items ();

$quiz = false;
foreach ($items as $item) {
	// combine inputs for quiz
	if ($item->type == courses\Item::QUIZ) {
		$quiz = true;
	} elseif (courses\Item::is_input ($item->type)) {
		$item->quiz = $quiz;
	} elseif ($quiz) {
		echo View::render ('courses/item/end_quiz', array ('answered' => false));
		$quiz = false;
	}

	// split options for choice fields
	if (in_array ((int) $item->type, array (courses\Item::DROP_DOWN, courses\Item::RADIO, courses\Item::CHECKBOXES))) {
		$item->content = explode ("\n", trim ($item->content));
	}

	echo View::render (
		'courses/item/' . $item->type,
		$item
	);
}

// close quiz if still open
if ($quiz) {
	echo View::render ('courses/item/end_quiz', array ('answered' => false));
}

?>