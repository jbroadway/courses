/**
 * Keeps track of the last page in a course that the user visited,
 * except the first page, and provides a link to jump them back there.
 * Uses the browser's local storage to do this.
 */
var lastpage = (function ($) {
	var self = {
			prefix: 'courses-lastpage-'
		},
		opts = {
			first_page: false,
			course_id: 0,
			page_id: 0,
			page_title: '',
			show_in_element: 'lastpage-msg',
			msg: 'Continue from where you last stopped'
		};
	
	self.init = function (options) {
		opts = $.extend ({}, opts, options);
		
		if (opts.first_page) {
			var lp = $.jStorage.get (self.prefix + opts.course_id);
			if (lp) {
				var pg = $.parseJSON (lp);
				if (pg.id != opts.page_id) {
					$('#' + opts.show_in_element)
						.html (opts.msg + ': <a href="' + pg.url + '">' + pg.title + '</a>')
						.show ();
				}
			}

		// update lastpage stored
		} else {
			var pg = {
				id: opts.page_id,
				url: window.location.href,
				title: opts.page_title
			}
			var lp = JSON.stringify (pg);
			$.jStorage.set (self.prefix + opts.course_id, lp);
		}
	};
	
	return self;
})(jQuery);
