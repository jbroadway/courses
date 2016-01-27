<?php

namespace courses;

/**
 * Custom view filters.
 */
class Filter {
	/**
	 * Filter status number to text.
	 */
	public static function status ($status) {
		return ($status == 1) ? __ ('Draft') : __ ('Published');
	}

	/**
	 * Filter availability number to text.
	 */
	public static function availability ($availability) {
		switch ($availability) {
			case 1:
				return __ ('Private');
			case 2:
				return __ ('Public - Free');
			case 3:
				return __ ('Public - Free w/ registration');
			case 4:
				return __ ('Public - Paid');
		}
	}

	/**
	 * Filter pricing float to money.
	 */
	public static function money ($price) {
		return money_format ('$%i', $price);
	}

	/**
	 * Filters -1 from the learner score.
	 */
	public static function learner_score ($score) {
		if ((int) $score === -1) {
			return '';
		}

		return $score;
	}

	/**
	 * Filters -1 from the learner passed status.
	 */
	public static function learner_passed ($passed) {
		if ((int) $passed === -1) {
			return '';
		}

		return ((int) $passed === 0) ? __ ('No') : __ ('Yes');
	}
}

?>