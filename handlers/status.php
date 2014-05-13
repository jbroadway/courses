<?php

/**
 * Display the status of a learner within a course.
 */

echo $tpl->render (
	'courses/status',
	array (
		'status' => courses\Data::learner_status ($data['course'], User::val ('id'))
	)
);

?>