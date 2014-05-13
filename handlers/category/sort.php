<?php

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';
$page->title = __ ('Sort Categories');
$page->add_style ('/apps/courses/css/admin.css');
$page->add_script ('/apps/courses/js/admin.js');
$page->add_script ('/js/jquery-ui/jquery-ui.min.js');

echo $tpl->render (
	'courses/category/sort',
	array (
		'categories' => courses\Category::sorted ()
	)
);

?>