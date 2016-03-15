<?php

namespace courses;

use DB;
use Model;
use User;

/**
 * Learner input data management belongs here.
 *
 * Fields:
 *
 * - id
 * - course
 * - user
 * - item
 * - status
 * - correct
 * - ts
 * - answer
 * - feedback
 */
class Data extends Model {
	public $table = '#prefix#courses_data';

	public static function for_learner ($course, $user) {
		return DB::fetch (
			'select * from #prefix#courses_data
			where course = ? and user = ?',
			$course,
			$user
		);
	}

	public static function for_items ($items, $user = false) {
		$user = $user ? $user : User::val ('id');
		return DB::fetch (
			'select * from #prefix#courses_data
			where item in(' . join (',', $items) . ')
			and user = ?',
			$user
		);
	}

	public static function remove_user ($course, $user) {
		return DB::execute (
			'delete from #prefix#courses_data
			where course = ?
			and user = ?',
			$course,
			$user
		);
	}

	public static function for_course ($course) {
		$inputs = Item::total_inputs ($course);
		$res = DB::pairs (
			'select user, sum(status) as progress
			from #prefix#courses_data where course = ? group by user',
			$course
		);
		foreach ($res as $k => $v) {
			$res[$k] = ($inputs > 0) ? ($v / $inputs) * 100 : 0;
			if ($res[$k] > 100) {
				$res[$k] = 100;
			}
		}
		return $res;
	}

	public static function learner_status ($course, $user = false) {
		$user = $user ? $user : User::val ('id');

		$inputs = Item::total_inputs ($course);
		if (! $inputs || $inputs === 0) {
			return -1;
		}

		$res = DB::shift (
			'select sum(status) from #prefix#courses_data
			where course = ? and user = ?',
			$course,
			$user
		);
		$res = ($res / $inputs) * 100;
		if ($res > 100) {
			$res = 100;
		}
		return $res;
	}
}

?>