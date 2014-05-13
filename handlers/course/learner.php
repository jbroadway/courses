<?php

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

$c = new courses\Course ($_GET['id']);
if ($c->error) {
	echo View::render ('courses/admin/error', $c);
	return;
}

$u = new User ($_GET['learner']);
if ($u->error) {
	echo View::render ('courses/admin/error', $u);
	return;
}

$items = courses\Item::get_inputs ($c->id);
$answers = courses\Data::for_learner ($c->id, $u->id);
$answers = is_array ($answers) ? $answers : array ();
foreach ($answers as $answer) {
	foreach ($items as $k => $item) {
		if ($item->id === $answer->item) {
			$items[$k]->answered = (int) $answer->status;
			$items[$k]->answer = $answer->answer;
			$items[$k]->correct = (int) $answer->correct;
			$items[$k]->answered_on = $answer->ts;
			$items[$k]->feedback = $answer->feedback;
			$items[$k]->data_id = $answer->id;
			break;
		}
	}
}

$page->title = $c->title . ' - ' . __ ('Learner') . ': ' . $u->name;

$page->add_script ('/apps/courses/js/admin.js');

echo View::render ('courses/course/learner', array (
	'course' => $c->id,
	'learner' => $u->id,
	'items' => $items
));

?>