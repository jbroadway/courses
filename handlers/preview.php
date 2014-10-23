<?php

/**
 * Preview a page from a course which may not be published yet.
 *
 * Usage:
 *
 *     /courses/preview/123/456
 */

$this->require_admin ();

if (count ($this->params) === 0) {
	echo $this->error (500, __ ('No course specified'), __ ('The link you requested is invalid.'));
	return;
}

$cid = $this->params[0];
$course = new courses\Course ($cid);
if ($course->error) {
	echo $this->error (404, __ ('Course not found'), __ ('The course you requested could not be found.'));
	return;
}

$is_instructor = false;
$is_learner = false;

if (! isset ($this->params[1])) {
	echo $this->error (404, __ ('Page not found'),  __ ('The page you requested could not be found.'));
	return;
}

$pid = $this->params[1];

$page->id = 'courses';
$page->title = $course->title;
$page->layout = Appconf::courses ('Courses', 'layout');
$page->add_script ('/apps/courses/css/default.css');
$page->add_script ('/apps/courses/css/items.css');

$pages = $course->pages ();

$page_body = '';
$p = new courses\Page ($pid);
$items = $p->items ();
$sections = array ();

// build a list of input item ids to fetch answers
$item_ids = array ();
foreach ($items as $k => $item) {
	if (in_array ((int) $item->type, courses\Item::$input_types)) {
		$item_ids[] = $item->id;
	} elseif ($item->type == 3) { // video
		$page->add_style ('/apps/courses/css/video-js.min.css');
		$page->add_script ('/apps/courses/js/video.min.js');
	} elseif ($item->type == 15) { // audio
		$page->add_style ('<style>.audiojs audio{display:none;}</style>');
		$page->add_script ('/apps/courses/js/audiojs/audio.min.js');
		$page->add_script ('<script>audiojs.events.ready(function(){audiojs.createAll();});</script>');
	} elseif ($item->type == 16 || $item->type == 17) { // section
		$sections['section-' . URLify::filter ($item->title)] = $item->title;
	}
}

// fetch answers for input items
$answers = array ();

$quiz = false;
$answered = false;
$instructor = ($course->instructor == User::val ('id'));
foreach ($items as $item) {
	$item->instructor = $instructor;

	// combine inputs for quiz
	if ($item->type == courses\Item::QUIZ) {
		$quiz = true;
	} elseif (courses\Item::is_input ($item->type)) {
		$item->quiz = $quiz;
		if ($item->answered) {
			$answered = true;
		}
	} elseif ($quiz) {
		$page_body .= View::render ('courses/item/end_quiz', array ('answered' => $answered));
		$quiz = false;
	}

	// split options for choice fields
	if (in_array ((int) $item->type, array (courses\Item::DROP_DOWN, courses\Item::RADIO, courses\Item::CHECKBOXES))) {
		$item->content = explode ("\n", trim ($item->content));
	}

	$page_body .= View::render (
		'courses/item/' . $item->type,
		$item
	);
}

// close quiz if still open
if ($quiz) {
	$page_body .= View::render ('courses/item/end_quiz', array ('answered' => $answered));
}

$page->add_script ('/apps/courses/js/api.js');
$page->add_script ('/apps/courses/js/course.js');

// determine previous and next pages
$page_ids = array_keys ($pages);
$page_count = count ($page_ids);
$prefix = '/courses/' . $course->id . '/' . URLify::filter ($course->title) . '/';
$previous = false;
$next = false;
for ($i = 0; $i < $page_count; $i++) {
	if ($pid == $page_ids[$i]) {
		if ($i > 0) {
			$previous = $prefix . $page_ids[$i - 1] . '/' . URLify::filter ($pages[$page_ids[$i - 1]]);
		}

		if ($i < $page_count - 1) {
			$next = $prefix . $page_ids[$i + 1] . '/' . URLify::filter ($pages[$page_ids[$i + 1]]);
		}

		break;
	}
}

// show a page
echo View::render (
	'courses/course/page',
	array (
		'course' => $course->id,
		'pages' => $pages,
		'id' => $pid,
		'title' => $p->title,
		'course_title' => $course->title,
		'page_body' => $page_body,
		'comments_id' => 'courses-course-' . $course->id . '-' . $pid,
		'has_glossary' => $course->has_glossary,
		'instructor' => $course->instructor,
		'is_instructor' => $is_instructor,
		'is_learner' => $is_learner,
		'has_sections' => count ($sections),
		'sections' => $sections,
		'previous' => $previous,
		'next' => $next
	)
);

?>