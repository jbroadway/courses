/**
 * Provides API access to the Courses backend.
 */
if (typeof courses === 'undefined') var courses = {};

courses.api = (function ($) {
	var self = {};
	
	// Prefix for API requests
	self.prefix = '/courses/api/';

	// Enable/disable debugging output to the console
	self.debug = false;
	
	// Helper function to verify parameters
	var _has = function (obj, prop) {
		return obj.hasOwnProperty (prop);
	};
	
	// Console log wrapper for debugging
	var _log = function (obj) {
		if (self.debug) {
			console.log (obj);
		}
		return obj;
	};
	
	// API call to /courses/api/data/submit
	self.data_submit = function (data, callback) {
		if (! _has (data, 'id')) {
			throw new Error ('courses.api.data_submit() - Missing parameter: id');
		}
		
		if (_has (data, 'quiz')) {
			if (! _has (data, 'answers')) {
				throw new Error ('courses.api.data_submit() - Missing parameter: answers');
			}

			$.post (
				_log (self.prefix + 'data/submit/' + encodeURIComponent (data.id)),
				_log ({ quiz: true, answers: data.answers }),
				callback
			);
		} else {
			if (! _has (data, 'answer')) {
				throw new Error ('courses.api.data_submit() - Missing parameter: answer');
			}
		
			$.post (
				_log (self.prefix + 'data/submit/' + encodeURIComponent (data.id)),
				_log ({ answer: data.answer }),
				callback
			);
		}
		
		return self;
	};
	
	return self;
})(jQuery);