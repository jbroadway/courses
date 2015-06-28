<?php

namespace courses;

use DB;
use Model;
use User;

class Course extends Model {
	public $table = '#prefix#courses_course';

	/**
	 * Delete a course and all its associated data.
	 */
	public function delete () {
		DB::beginTransaction ();
		DB::execute ('delete from #prefix#courses_data where course = ?', $this->id);
		DB::execute ('delete from #prefix#courses_learner where course = ?', $this->id);
		DB::execute ('delete from #prefix#courses_item where course = ?', $this->id);
		DB::execute ('delete from #prefix#courses_page where course = ?', $this->id);
		DB::execute ('delete from #prefix#courses_course where id = ?', $this->id);
		$this->error = DB::error ();
		if ($this->error) {
			DB::rollback ();
			return false;
		}
		return DB::commit ();
	}

	/**
	 * Get all courses grouped by category, optionally limited by owner.
	 */
	public static function categories ($owner = false, $published = false) {
		$sql = 'select
					#prefix#courses_course.*, #prefix#courses_category.title as category_title
				from
					#prefix#courses_course, #prefix#courses_category
				where
					#prefix#courses_course.category = #prefix#courses_category.id';

		if ($owner) {
			$sql .= ' and #prefix#courses_course.owner = ?';
		}

		if ($published) {
			$sql .= ' and #prefix#courses_course.status = 2 and #prefix#courses_course.availability > 1';
		}

		$sql .= ' order by
					#prefix#courses_category.sorting asc,
					#prefix#courses_course.sorting asc';

		if ($owner) {
			return DB::fetch ($sql, $owner);
		}
		return DB::fetch ($sql);
	}

	/**
	 * Get an associative array of pages for the current course.
	 */
	public function pages () {
		return DB::pairs (
			'select id, title from #prefix#courses_page where course = ? order by sorting asc',
			$this->id
		);
	}

	/**
	 * Fetches a list of learners in a course. Returns their user
	 * ID, name, and email.
	 */
	public function learners () {
		return DB::fetch (
			'select
				#prefix#user.id as id,
				#prefix#user.name as name,
				#prefix#user.email as email
			from
				#prefix#user, #prefix#courses_learner
			where
				#prefix#courses_learner.course = ? and
				#prefix#courses_learner.user = #prefix#user.id
			order by
				#prefix#user.name asc',
			$this->id
		);
	}

	/**
	 * Fetch all words and their definitions for this course.
	 */
	public function glossary () {
		return DB::pairs (
			'select title, content
			from #prefix#courses_item
			where course = ? and type = ?
			order by title asc',
			$this->id,
			Item::DEFINITION
		);
	}

	/**
	 * Return a list of courses that the current user is instructing.
	 */
	public static function instructing ($user = false) {
		if (! $user) $user = User::current ();
		if (is_object ($user)) $user = $user->id;

		return DB::fetch (
			'select * from #prefix#courses_course
			where instructor = ?
			order by title asc',
			$user
		);
	}
	
	/**
	 * Get the course's discount price based on the specified discount
	 * value, which is an integer.
	 */
	public function discount_price ($discount = 0) {
		if ($discount > 0) {
			return $this->price - ($this->price * ($discount / 100));
		}
		return $this->price;
	}
}

?>