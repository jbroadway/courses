<?php

$this->require_acl ('admin', 'courses');

$page->layout = 'admin';

$cur = $this->installed ('courses', $appconf['Admin']['version']);

if ($cur === true) {
	$page->title = 'Already installed';
	echo '<p><a href="/courses/admin">Continue</a></p>';
	return;
} elseif ($cur !== false) {
	header ('Location: /' . $appconf['Admin']['upgrade']);
	exit;
}

$page->title = 'Installing App: Courses';

$conn = conf ('Database', 'master');
$driver = $conn['driver'];
DB::beginTransaction ();

$error = false;
$sqldata = sql_split (file_get_contents ('apps/courses/conf/install_' . $driver . '.sql'));
foreach ($sqldata as $sql) {
	if (! DB::execute ($sql)) {
		$error = DB::error ();
		break;
	}
}

if ($error) {
	DB::rollback ();
	echo '<p class="visible-notice">Error: ' . $error . '</p>';
	echo '<p>Install failed.</p>';
	return;
}
DB::commit ();

echo '<p><a href="/courses/admin">Done.</a></p>';

$this->mark_installed ('courses', $appconf['Admin']['version']);

?>