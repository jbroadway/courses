<?php

$this->require_acl ('admin', 'courses');

$course = new courses\Course ($_GET['id']);

$page->title = sprintf (
    '%s: %s',
    __ ('Message Learners'),
    $course->title
);
$page->layout = 'admin';

$form = new Form ('post', $this);

$form->data = array (
	'id' => $_GET['id']
);

echo $form->handle (function ($form) use ($page, $course) {
	$learners = $course->learners ();

	$count = 0;

	try {
		set_time_limit (0);

		foreach ($learners as $learner) {
			Mailer::send (array (
				'to' => array ($learner->email, $learner->name),
				'subject' => $_POST['subject'],
				'text' => $_POST['body']
			));

			$count++;
		}
	} catch (Exception $e) {
		error_log ('Error sending message: ' . $e->getMessage ());
		$form->failed[] = 'email';
		return false;
	}
	
	$form->controller->add_notification (__ ('%d messages sent.', $count));
	$form->controller->redirect ('/courses/course/learners?id=' . Template::sanitize ($_GET['id']));
});
