<?php

/**
 * Export all inputs and feedback for a single learner.
 */

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
			$items[$k]->answered_on = $answer->ts;
			$items[$k]->feedback = $answer->feedback;
			$items[$k]->data_id = $answer->id;

			$correct = (int) $answer->correct;
			if ($correct == -1) {
				$items[$k]->correct = __ ('No');
			} elseif ($correct == 1) {
				$items[$k]->correct = __ ('Yes');
			} else {
				$items[$k]->correct = __ ('N/A');
			}
			break;
		}
	}
}

function export_learner_sort ($a, $b) {
	if ($a->answered_on == $b->answered_on) {
		return 0;
	}
	
	return ($a->answered_on < $b->answered_on) ? -1 : 1;
}

usort ($items, 'export_learner_sort');		

$page->layout = false;
header ('Cache-control: private');
header ('Content-Type: text/plain');
header ('Content-Disposition: attachment; filename=learner-data-' . $u->id . '-' . URLify::filter ($u->name) . '.csv');

echo "\"Course\",\"Question ID\",\"Timestamp\",\"Correct\",\"Question\",\"Answer\",\"Feedback\"\n";

foreach ($items as $item) {
	if (! $item->answered) {
		continue;
	}

	$row = array (
		$c->title,
		$item->id,
		$item->answered_on,
		$item->correct,
		$item->title,
		$item->answer,
		$item->feedback
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