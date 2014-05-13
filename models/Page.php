<?php

namespace courses;

use DB, Model;

class Page extends Model {
	public $table = '#prefix#courses_page';

	/**
	 * Override `Model::next()` to limit by course ID.
	 */
	public function next ($field = 'sorted') {
		if ($field === false) {
			$field = $this->key;
		}
		
		$res = DB::shift (
			'select (' . Model::backticks ($field) . ' + 1)' .
			' from ' . Model::backticks ($this->table) .
			' where ' . Model::backticks ('course') . ' = ?' .
			' order by ' . Model::backticks ($field) . ' desc' .
			' limit 1',
			$this->course
		);
		if (! $res) {
			return 1;
		}
		return $res;
	}

	/**
	 * Fetch all items on a page.
	 */
	public function items () {
		return Item::query ()
			->where ('page', $this->id)
			->order ('sorting', 'asc')
			->fetch_orig ();
	}
}

?>