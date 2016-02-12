<?php

namespace courses;

use DB;
use User;

/**
 * Helpers for learner management.
 */
class Learner {
	/**
	 * Update the score of a learner.
	 */
	public static function update_score ($user, $score) {
		if ($score == '') {
			$score = -1;
		}

		return DB::execute (
			'update #prefix#courses_learner set score = ? where user = ?',
			$score,
			$user
		);
	}
	/**
	 * Update the passed status of a learner.
	 */
	public static function update_passed ($user, $passed) {
		if ($passed == '') {
			$passed = -1;
		}

		return DB::execute (
			'update #prefix#courses_learner set passed = ? where user = ?',
			$passed,
			$user
		);
	}

	/**
	 * Get a learner by username.
	 */
	public static function get ($user) {
		return DB::single (
			'select * from #prefix#courses_learner where user = ?',
			$user
		);
	}
	
	/**
	 * Checks if a user is registered for the specified course.
	 * If no user is specified, it will use the current user.
	 */
	public static function in_course ($course, $user = false) {
		$user = $user ? $user : User::val ('id');

		return (int) DB::shift (
			'select count(*) from #prefix#courses_learner where user = ? and course = ?',
			$user,
			$course
		);
	}

	/**
	 * Adds a user to a course. If no user is specified, it will
	 * use the current user.
	 */
	public static function add_to_course ($course, $user = false) {
		$user = $user ? $user : User::val ('id');
		
		$res = DB::execute (
			'insert into #prefix#courses_learner (user, course, ts) values (?, ?, ?)',
			$user,
			$course,
			gmdate ('Y-m-d H:i:s')
		);

		return $res;
	}

	/**
	 * Removes a user from a course. If no user is specified, it will
	 * use the current user.
	 */
	public static function remove_from_course ($course, $user = false) {
		$user = $user ? $user : User::val ('id');
		
		$res = DB::execute (
			'delete from #prefix#courses_learner where user = ? and course = ?',
			$user,
			$course
		);
		
		// remove records for input tracking
		if ($res) {
			Data::remove_user ($course, $user);
		}
		
		return $res;
	}

	/**
	 * Fetches a list of courses a learner currently belongs to.
	 * If no user is specified, it will use the current user.
	 */
	public static function courses ($user = false) {
		$user = $user ? $user : User::val ('id');

		return DB::fetch (
			'select
				#prefix#courses_course.*
			from
				#prefix#courses_learner, #prefix#courses_course
			where
				#prefix#courses_learner.user = ? and
				#prefix#courses_learner.course = #prefix#courses_course.id and
				#prefix#courses_course.status = 2
			order by
				#prefix#courses_course.title asc',
			$user
		);
	}
}

?>
