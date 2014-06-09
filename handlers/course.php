<?php

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

// Was this course published yet?
if ((int) $course->status < 2) {
	echo $this->error (404, __ ('Course not found'), __ ('The course you requested could not be found.'));
	return;
}

if (User::is_valid ()) {
	$is_instructor = ($course->instructor == User::val ('id'));
	$is_learner = (! $instructor) ? courses\Learner::in_course ($cid) : false;
} else {
	$is_instructor = false;
	$is_learner = false;
}

if (((int) $course->availability === 2 && $_SERVER['REQUEST_METHOD'] === 'GET') || $is_instructor || $is_learner) {
	// free or already registered, show the course
	$page->title = $course->title;
	$page->layout = $appconf['Courses']['layout'];
	$page->add_script ('/apps/courses/css/default.css');
	$page->add_script ('/apps/courses/css/items.css');

	$pages = $course->pages ();

	if (! isset ($this->params[2])) {
		foreach ($pages as $id => $title) {
			$this->redirect (preg_replace ('|^/courses|', '/courses', $_SERVER['REQUEST_URI']) . '/' . $id . '/' . URLify::filter ($title));
		}

		// show the table of contents
		/*echo View::render (
			'courses/course/toc',
			array (
				'id' => $course->id,
				'title' => $course->title,
				'pages' => $pages,
			)
		);*/
	}

	if ($this->params[2] === 'contact') {
		// contact the instructor
		$course = $course->orig ();
		$course->pages = $pages;
		$course->is_instructor = $is_instructor;
		$course->is_learner = $is_learner;
		echo $this->run ('courses/course/contact', $course);
		return;
	} elseif ($this->params[2] === 'glossary') {
		// show the glossary
		echo View::render (
			'courses/course/glossary',
			array (
				'course' => $course->id,
				'course_title' => $course->title,
				'pages' => $pages,
				'glossary' => $course->glossary (),
				'has_glossary' => $course->has_glossary,
				'instructor' => $course->instructor,
				'is_instructor' => $is_instructor,
				'is_learner' => $is_learner,
				'comments_id' => 'courses-course-' . $course->id . '-glossary'
			)
		);
		return;
	}

	$pid = $this->params[2];
	if (! isset ($pages[$pid])) {
		echo $this->error (404, __ ('Page not found'), __ ('The page you requested could not be found.'));
		return;
	}

	// build the page body
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
	$answers = courses\Data::for_items ($item_ids);
	$answers = is_array ($answers) ? $answers : array ();
	foreach ($answers as $answer) {
		foreach ($items as $k => $item) {
			if ($item->id === $answer->item) {
				$items[$k]->answered = (int) $answer->status;
				$items[$k]->answer = $answer->answer;
				$items[$k]->correct = (int) $answer->correct;
				break;
			}
		}
	}

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
			'sections' => $sections
		)
	);

	return;
}

switch ((int) $course->availability) {
	case 1:
		// private, show login or 404
		
		// show login form
		if (! User::is_valid ()) {
			$this->redirect ('/user/login?redirect=' . urlencode ($_SERVER['REQUEST_URI']));
		}

		// still no access, show 404
		echo $this->error (404, __ ('Course not found'), __ ('The course you requested could not be found.'));
		return;

	case 2:
	case 3:
		// free or free w/ registration, show summary and login/join link
		
		// show login form
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (! User::is_valid ()) {
				$this->redirect ('/user/login?redirect=' . urlencode ($_SERVER['REQUEST_URI']));
			}

			// add learner
			if ($_POST['subscribe'] == 1) {
				$res = courses\Learner::add_to_course ($course->id, User::val ('id'));
				if (! $res) {
					error_log (DB::error ());
					echo $this->error (404, __ ('An error occurred'), __ ('There was an error in the course registration. Please try again later.'));
					return;
				}

				// reload to show course
				$this->redirect ($_SERVER['REQUEST_URI']);
				return;
			}
		}
		
		// show summary
		$page->title = $course->title;
		$page->layout = $appconf['Courses']['layout'];
		$page->add_script ('/apps/courses/css/default.css');
		echo View::render ('courses/course/summary', $course);
		return;

	case 4:
		// paid, show summary and login/purchase link
		$page->title = $course->title;
		$page->layout = $appconf['Courses']['layout'];
		$page->add_script ('/apps/courses/css/default.css');
		echo View::render ('courses/course/summary', $course);

		// show login form
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (! User::is_valid ()) {
				$this->redirect ('/user/login?redirect=' . urlencode ($_SERVER['REQUEST_URI']));
			}

			// Show pay wall
			$handler = $appconf['Courses']['payment_handler'];
			$handler = ($handler === '') ? false : $handler;
			$controller = $this;
			if ($handler) {
				printf ('<h3>%s</h3>', __ ('Payment information'));
				echo $this->run ($handler, array (
					'amount' => $course->price * 100,
					'description' => 'Course: ' . $course->title,
					'callback' => function ($charge, $payment) use ($course, $controller) {
						$res = courses\Learner::add_to_course ($course->id, User::val ('id'));
						if (! $res) {
							error_log (DB::error ());
							echo $controller->error (404, __ ('An error occurred'), __ ('There was an error in the course registration. Please contact the administrator of the site to assist you.'));
							return;
						}

						// email receipt
						try {
							$user = User::current ();

							Mailer::send (array (
								'to' => array ($user->email, $user->name),
								'subject' => 'Payment receipt for course: ' . $course->title,
								'text' => View::render ('courses/email/receipt', array (
									'user' => $user,
									'course' => $course,
									'payment' => $payment
								))
							));
						} catch (Exception $e) {
							error_log ('Mail error: ' . $e->getMessage ());
						}

						// reload to show course
						$controller->redirect ($_SERVER['REQUEST_URI']);
					}
				));
			} else {
				// No payment processor
				printf ('<p class="visible-notice">%s</p>', __ ('No payment processor has been configured for this site.'));
			}
		}

		return;
}

?>