<?php

require_once ('lib/Autoloader.php');

class CoursesTest extends AppTest {
	public static function setUpBeforeClass () {
		parent::setUpBeforeClass ();
		
		foreach (sql_split (file_get_contents ('apps/courses/conf/install_sqlite.sql')) as $sql) {
			DB::execute ($sql);
		}
	}

	public function test_admin () {
		$this->userAdmin ();
		$res = $this->get ('courses/admin');

		$this->assertContains ('Add Category', $res);

		global $page;
		$this->assertEquals ('Courses', $page->title);
	}
}

?>