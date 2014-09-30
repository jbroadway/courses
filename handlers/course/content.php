<?php

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

$lock = new Lock ('courses_page', $_GET['id'] . '/' . $_GET['page']);
if ($lock->exists ()) {
	$page->title = __ ('Editing Locked');
	echo $tpl->render ('admin/locked', $lock->info ());
	return;
} else {
	$lock->add ();
}

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

try {
	$scorm_modules = scorm\Util::get_modules ();
} catch (Exception $e) {
	$scorm_modules = array ();
}

$page->title = __ ('Editing Page') . ': ' . $c->title . ' - ' . $p->title;

$this->run ('admin/util/fontawesome');
$this->run ('filemanager/util/browser');

$page->add_style ('/apps/admin/js/redactor/redactor.css');
$page->add_style ('/apps/courses/css/redactor.css');
$page->add_style ('/apps/courses/js/codemirror/lib/codemirror.css');
$page->add_style ('/apps/courses/css/video-js.min.css');
$page->add_style ('/apps/courses/css/admin.css');
$page->add_style ('/apps/courses/css/items.css');

$page->add_script ('/apps/courses/js/waypoints.min.js');
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');
$page->add_script ('/apps/courses/js/knockout-2.2.0.min.js');
$page->add_script ('/apps/courses/js/knockout-sortable.min.js');
$page->add_script ('/apps/admin/js/redactor/redactor.min.js');
$page->add_script ('/apps/admin/js/redactor/plugins/imagebrowser.js');
$page->add_script ('/apps/courses/js/codemirror/lib/codemirror.js');
$page->add_script ('/apps/courses/js/video.min.js');
$page->add_script ('/apps/courses/js/audiojs/audio.min.js');
$page->add_script ('/apps/courses/js/codemirror/mode/xml/xml.js');
$page->add_script ('/apps/courses/js/codemirror/mode/javascript/javascript.js');
$page->add_script ('/apps/courses/js/codemirror/mode/css/css.js');
$page->add_script ('/apps/courses/js/codemirror/mode/htmlmixed/htmlmixed.js');
$page->add_script ('/apps/scorm/js/scorm.js');
$page->add_script ('/apps/courses/js/admin.js');
$page->add_script ('/apps/courses/js/editor.js');

$page->add_script (I18n::export (
	'Are you sure you want to delete this item?',
	'Choose an image',
	'Choose a video',
	'Choose a file',
	'Loading...',
	'Untitled',
	'Text',
	'Image',
	'Video',
	'Audio',
	'HTML code',
	'Pre-formatted text',
	'SCORM module',
	'File download',
	'Accordion',
	'Definition',
	'Single-line answer',
	'Paragraph answer',
	'Drop down',
	'Checkboxes',
	'Multiple choice',
	'File upload'
));

echo View::render (
	'courses/course/content',
	array (
		'course' => $c->id,
		'published' => ($c->status == 1) ? false : true,
		'page' => $p->id,
		'items' => $p->items (),
		'scorm_modules' => $scorm_modules
	)
);

?>