<?php

$this->require_acl ('admin', 'courses', 'settings');

require_once ('apps/admin/lib/Functions.php');

$page->layout = 'admin';
$page->title = __ ('Courses - Settings');

$form = new Form ('post', $this);

$form->data = array (
	'title' => $appconf['Courses']['title'],
	'public_name' => $appconf['Courses']['public_name'],
	'layout' => $appconf['Courses']['layout'],
	'course_layout' => $appconf['Courses']['course_layout'],
	'comments' => $appconf['Courses']['comments'],
	'payment_handler' => $appconf['Courses']['payment_handler'],
	'payment_handlers' => courses\App::payment_handlers ()
);

echo $form->handle (function ($form) {
	$merged = Appconf::merge ('courses', array (
		'Courses' => array (
			'title' => $_POST['title'],
			'public_name' => $_POST['public_name'],
			'layout' => $_POST['layout'],
			'course_layout' => $_POST['course_layout'],
			'comments' => ($_POST['comments'] === 'yes') ? true : false,
			'payment_handler' => $_POST['payment_handler']
		)
	));

	if (! Ini::write ($merged, 'conf/app.courses.' . ELEFANT_ENV . '.php')) {
		printf ('<p>%s</p>', __ ('Unable to save changes. Check your folder permissions and try again.'));
		return;
	}

	$form->controller->add_notification (__ ('Settings saved.'));
	$form->controller->redirect ('/courses/admin');
});

?>