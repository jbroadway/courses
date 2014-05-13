<?php

/**
 * Duplicate a course.
 */

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

// fetch the course
$c = new courses\Course ($_GET['id']);
if ($c->error) {
	echo View::render ('courses/admin/error', $c);
	return;
}

DB::beginTransaction ();

$data = $c->orig ();
unset ($data->id);
$data->title .= ' (copy)';
$data->status = 1;
$data->sorting = $c->next ('sorting');
$c2 = new courses\Course ($data);

if (! $c2->put ()) {
	DB::rollback ();
	echo View::render ('courses/admin/error', $c2);
	return;
}

$old_course_id = $c->id;
$new_course_id = $c2->id;
unset ($c);
unset ($c2);
unset ($data);

$pages = courses\Page::query ()
	->where ('course', $old_course_id)
	->fetch_orig ();

for ($j = 0; $j < count ($pages); $j++) {
	$old_page_id = $pages[$j]->id;
	$data = $pages[$j];
	unset ($data->id);
	$data->course = $new_course_id;
	$p = new courses\Page ($data);

	if (! $p->put ()) {
		DB::rollback ();
		echo View::render ('courses/admin/error', $p);
		return;
	}

	$new_page_id = $p->id;
	unset ($p);
	unset ($data);
	$pages[$j] = null;

	$items = courses\Item::query ()
		->where ('page', $old_page_id)
		->fetch_orig ();

	for ($k = 0; $k < count ($items); $k++) {
		$id = $items[$k]->id;
		$data = $items[$k];
		unset ($data->id);
		$data->page = $new_page_id;
		$data->course = $new_course_id;
		$i = new courses\Item ($data);
		
		if (! $i->put ()) {
			DB::rollback ();
			echo View::render ('courses/admin/error', $i);
			return;
		}

		unset ($i);
		unset ($data);
		$items[$k] = null;
	}

	unset ($items);
}

unset ($pages);

DB::commit ();

$this->add_notification (__ ('Course duplicated.'));
$this->redirect ('/courses/admin');

?>