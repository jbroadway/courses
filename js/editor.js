var editor = (function ($) {
	var self = {};

	/**
	 * Prefix to API URL.
	 */
	self.prefix = '/courses/api/';
	
	/**
	 * Is the editor initialized.
	 */
	self.initialized = false;
	
	/**
	 * The ID of the course being edited.
	 */
	self.course = null;
	
	/**
	 * The ID of the page being edited.
	 */
	self.page = null;
	
	/**
	 * The list of items.
	 */
	self.items = null;
	
	/**
	 * The preview window object.
	 */
	self.preview_window = null;
	
	/**
	 * Defaults for the various item types.
	 */
	self.defaults = {
		2:  { content: '/apps/courses/pix/default.png' },
		3:  { content: '/apps/courses/css/blank.mp4' },
		15: { content: '/apps/courses/css/blank.mp3' }
	};
	
	/**
	 * List of translatable strings.
	 */
	self.str = {
		delete_confirm: $.i18n ('Are you sure you want to delete this item?'),
		untitled: $.i18n ('Untitled'),
		loading: $.i18n ('Loading...'),
		choose_image: $.i18n ('Choose an image'),
		choose_video: $.i18n ('Choose a video'),
		choose_audio: $.i18n ('Choose an audio clip'),
		choose_file: $.i18n ('Choose a file')
	};

	/**
	 * Type names for display.
	 */
	self.type_names = {
		1: $.i18n ('Text'),
		2: $.i18n ('Image'),
		3: $.i18n ('Video'),
		4: $.i18n ('HTML code'),
		5: $.i18n ('Pre-formatted text'),
		6: $.i18n ('SCORM module'),
		7: $.i18n ('File download'),
		8: $.i18n ('Click-to-reveal'),
		9: $.i18n ('Definition'),
		10: $.i18n ('Single-line answer'),
		11: $.i18n ('Paragraph answer'),
		12: $.i18n ('Drop down'),
		13: $.i18n ('Multiple choice'),
		14: $.i18n ('Checkboxes'),
		15: $.i18n ('Audio'),
		16: $.i18n ('Section'),
		17: $.i18n ('Quiz'),
		18: $.i18n ('File upload')
	};

	self.type_icons = {
		1: '<i class="fa fa-pencil"></i>',
		2: '<i class="fa fa-picture-o"></i>',
		3: '<i class="fa fa-film"></i>',
		4: '<i class="fa fa-code"></i>',
		5: '<i class="fa fa-quote-left"></i>',
		6: '<i class="fa fa-cogs"></i>',
		7: '<i class="fa fa-download"></i>',
		8: '<i class="fa fa-plus-circle"></i>',
		9: '<i class="fa fa-lightbulb-o"></i>',
		10: '<i class="fa fa-edit"></i>',
		11: '<i class="fa fa-edit"></i>',
		12: '<i class="fa fa-arrow-circle-down"></i>',
		13: '<i class="fa fa-check-circle-o"></i>',
		14: '<i class="fa fa-check"></i>',
		15: '<i class="fa fa-headphones"></i>',
		16: '<i class="fa fa-bookmark"></i>',
		17: '<i class="fa fa-star"></i>',
		18: '<i class="fa fa-cloud-upload"></i>'
	};

	/**
	 * Options for the wysiwyg editor.
	 */
	self.redactor_options = {
		autoresize: false,
		plugins: ['imagebrowser'],
		buttons: [
			'html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|',
			'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
			'table', 'link', 'imagebrowser', '|', 'fontcolor', 'backcolor', '|',
			'alignment', 'horizontalrule'
		]
	};
	
	/**
	 * Whether the items are currently being updated. Prevents
	 * calling update_items() incorrectly.
	 */
	self.updating_items = false;
	
	/**
	 * Manages CodeMirror instances by item ID.
	 */
	self.codemirror_instances = [];
	
	/**
	 * A list of SCORM modules available to the SCORM item type.
	 */
	self.scorm_modules = [];
	
	/**
	 * Initialize the data and settings.
	 */
	self.init = function (options) {
		self.items = self.make_items_observable (options.items);
		self.course = options.course;
		self.page = options.page;
		self.scorm_modules = options.scorm_modules;

		ko.bindingHandlers.sortable.afterMove = self.sortable_update;

		ko.applyBindings (self);
		self.initialized = true;

		// register event handlers
		$('.main').on ('blur', '.redactor-editor', self.wysiwyg_update);

		self.show_full ();
	};

	/**
	 * Initialize or reinitialize plugins (wysiwyg editor, syntax highlighter, etc.).
	 */
	self.initialize_plugins = function () {
		$('.wysiwyg').redactor (self.redactor_options);
		_V_.options.flash.swf = '/apps/courses/css/video-js.swf';
		$('video').each (function () {
			_V_ ($(this).attr ('id'));
		});
		audiojs.createAll ();

		$('.html').each (function () {
			var id = $(this).data ('id');
			if (id in self.codemirror_instances) {
				self.codemirror_instances[id].toTextArea ();
			}
			self.codemirror_instances[id] = CodeMirror.fromTextArea (this, {
				mode: 'text/html',
				lineNumbers: true,
				indentWithTabs: true
			});
			self.codemirror_instances[id].on ('blur', self.codemirror_update);
		});

		$('.pre').each (function () {
			var id = $(this).data ('id');
			if (id in self.codemirror_instances) {
				self.codemirror_instances[id].toTextArea ();
			}
			self.codemirror_instances[id] = CodeMirror.fromTextArea (this, {
				mode: 'text/plain',
				lineNumbers: true,
				indentWithTabs: true
			});
			self.codemirror_instances[id].on ('blur', self.codemirror_update);
		});
	};

	/**
	 * Make an observable item from a regular one.
	 */
	self.make_item_observable = function (item) {
		var i = {
			id: item.id,
			title: ko.observable (item.title),
			page: item.page,
			sorting: ko.observable (item.sorting),
			type: item.type,
			content: ko.observable (item.content),
			answer: ko.observable (item.answer)
		};

		/**
		 * The name of this item, or Untitled.
		 */
		i.sortable_title = ko.computed (function () {
			if (this.title () === '') {
				return self.str.untitled;
			}
			return this.title ();
		}, i);

		/**
		 * Which template to call for this item type.
		 */
		i.template = ko.computed (function () {
			return self.template_name (this);
		}, i);

		/**
		 * Get the display name for an item type.
		 * Accepts an item object or a type number.
		 */
		i.type_name = ko.computed (function () {
			return self.type_names[this.type];
		}, i);

		i.type_icon = ko.computed (function () {
			return self.type_icons[this.type];
		}, i);

		/**
		 * The image to display, or a temp image.
		 */
		i.img_src = ko.computed (function () {
			if (this.content () === '') {
				return self.defaults[2].content;
			}
			return this.content ();
		}, i);

		/**
		 * Answers for a choice question.
		 */
		i.answers = ko.computed (function () {
			return this.content ().split ("\n");
		}, i);

		return i;
	};

	/**
	 * Turns the items into an observableArray whose properties are also observable.
	 */
	self.make_items_observable = function (data) {
		var items = ko.observableArray ();
		for (var i = 0; i < data.length; i++) {
			items.push (self.make_item_observable (data[i]));
		};
		return items;
	};

	/**
	 * Determine the template ID for an item type.
	 * Accepts an item object or a type number.
	 */	
	self.template_name = function (item) {
		if (item && item.type) {
			return 'tpl-item-' + item.type;
		} else if (item) {
			return 'tpl-item=' + item;
		}
	};

	/**
	 * Delete an item from the list.
	 */
	self.delete_item = function () {
		var item = this;

		if (confirm (self.str.delete_confirm)) {
			self.updating_items = true;
			self.show_saving ();

			$.post (
				self.prefix + 'item/delete',
				{course: self.course, page: self.page, item: item.id},
				function (res) {
					self.updating_items = false;
					self.done_saving ();

					if (! res.success) {
						$.add_notification (res.error);
					} else {
						if (self.codemirror_instances[item.id]) {
							self.codemirror_instances.splice (item.id, 1);
						}
						self.items.remove (item);
					}
				}
			);
		}

		return false;
	};

	/**
	 * Save the updated items to the server.
	 */
	self.update_items = function () {
		if (! self.initialized) {
			return false;
		}
		
		if (self.updating_items) {
			return false;
		}

		self.updating_items = true;
		self.show_saving ();

		var _items = ko.toJS (self.items),
			items = [];
		for (var i = 0; i < _items.length; i++) {
			var item = {
				id: _items[i].id,
				title: _items[i].title,
				sorting: i + 1,
				content: _items[i].content,
				answer: _items[i].answer
			};
			items.push (item);
		}

		$.post (
			self.prefix + 'item/update_all',
			{course: self.course, page: self.page, items: items},
			function (res) {
				self.updating_items = false;
				self.done_saving ();
	
				if (! res.success) {
					$.add_notification (res.error);
				} else {
					self.refresh_preview ();
				}
			}
		);

		return false;
	};

	/**
	 * Update items after re-initializing editor from
	 * sorting items..
	 */
	self.sortable_update = function () {
		self.update_items ();
	};

	/**
	 * Update items after wysiwyg editor loses focus,
	 * then call editor.update_items().
	 */
	self.wysiwyg_update = function () {
		$('.wysiwyg').each (function () {
			var id = $(this).data ('id'),
				html = $(this).redactor ('code.get');

			for (var i in self.items ()) {
				if (self.items ()[i].id == id) {
					self.items ()[i].content (html);
					break;
				}
			}
		});

		self.update_items ();
	};

	/**
	 * Update items after codemirror editor loses focus,
	 * then call editor.update_items().
	 */
	self.codemirror_update = function (cm) {
		var textarea = cm.getTextArea (),
			id = $(textarea).data ('id'),
			html = cm.getValue ();
		
		for (var i in self.items ()) {
			if (self.items ()[i].id == id) {
				self.items ()[i].content (html);
				break;
			}
		}

		self.update_items ();
	};

	/**
	 * Update the preview tab with the latest page contents.
	 */
	self.show_preview = function () {
		var url = '/courses/preview/' + self.course + '/' + self.page;
		
		if (self.preview_window === null || self.preview_window.closed) {
			self.preview_window = window.open (url, 'elefant-preview', '');
		} else {
			self.preview_window.location.reload (true);
		}
		
		return false;
	};
	
	/**
	 * Update the preview tab if one is open.
	 */
	self.refresh_preview = function () {
		if (self.preview_window !== null && ! self.preview_window.closed) {
			self.preview_window.location.reload (true);
		}
	};

	/**
	 * Open the file browser for an image file.
	 */
	self.filemanager_image = function () {
		var item = this;

		$.filebrowser ({
			title: self.str.choose_image,
			thumbs: true,
			callback: function (file) {
				item.content (file);
				self.update_items ();
			}
		});
	};

	/**
	 * Open the file browser for a video file.
	 */
	self.filemanager_video = function () {
		var item = this;

		$.filebrowser ({
			title: self.str.choose_video,
			allowed: ['mp4', 'm4v', 'flv', 'f4v'],
			callback: function (file) {
				item.content (file);
				self.update_items ();
				var vid = _V_ ('video-' + item.id);
				vid.src (file);
			}
		});
	};

	/**
	 * Open the file browser for an image file.
	 */
	self.filemanager_cover = function () {
		var item = this;

		$.filebrowser ({
			title: self.str.choose_image,
			thumbs: true,
			callback: function (file) {
				item.answer (file);
				self.update_items ();
			}
		});
	};

	/**
	 * Open the file browser for an audio file.
	 */
	self.filemanager_audio = function () {
		var item = this;

		$.filebrowser ({
			title: self.str.choose_audio,
			allowed: ['mp3'],
			callback: function (file) {
				item.content (file);
				self.update_items ();
				for (var i in audiojs.instances) {
					if ($(audiojs.instances[i].element).attr ('id') === 'audio-' + item.id) {
						audiojs.instances[i].load (file);
						break;
					}
				}
			}
		});
	};

	/**
	 * Open the file browser for any file type.
	 */
	self.filemanager_file = function () {
		var item = this;

		$.filebrowser ({
			title: self.str.choose_file,
			callback: function (file) {
				item.content (file);
				self.update_items ();
			}
		});
	};

	/**
	 * Get the next sorting value.
	 */
	self.next = function () {
		var sorting = 0;
		for (var i in self.items ()) {
			var current = parseInt (self.items ()[i].sorting ());
			if (sorting < current) {
				sorting = current;
			}
		}
		return sorting + 1;
	}

	/**
	 * Add an item on the server so it has an ID value.
	 */
	self.create_blank_item = function (type) {
		self.show_full ();

		var item = {
			title: '',
			page: self.page,
			sorting: self.next (),
			type: type,
			content: '',
			answer: ''
		};

		if (self.defaults[type]) {
			item = $.extend (item, self.defaults[type]);
		}

		$.post (self.prefix + 'item/create', item, function (res) {
			if (! res.success) {
				$.add_notification (res.error);
				return;
			}
			self.items.push (self.make_item_observable (res.data));
			self.initialize_plugins ();
			self.focus_last_item ();
		});
		return false;
	};

	/**
	 * Add an item by type.
	 */
	self.add_text_field			= function () { return self.create_blank_item (1); };
	self.add_image_field		= function () { return self.create_blank_item (2); };
	self.add_video_field		= function () { return self.create_blank_item (3); };
	self.add_html_field			= function () { return self.create_blank_item (4); };
	self.add_pre_field			= function () { return self.create_blank_item (5); };
	self.add_scorm_field		= function () { return self.create_blank_item (6); };
	self.add_file_field			= function () { return self.create_blank_item (7); };
	self.add_reveal_field		= function () { return self.create_blank_item (8); };
	self.add_definition_field	= function () { return self.create_blank_item (9); };
	self.add_input_field		= function () { return self.create_blank_item (10); };
	self.add_para_field			= function () { return self.create_blank_item (11); };
	self.add_drop_field			= function () { return self.create_blank_item (12); };
	self.add_multi_field		= function () { return self.create_blank_item (13); };
	self.add_check_field		= function () { return self.create_blank_item (14); };
	self.add_audio_field		= function () { return self.create_blank_item (15); };
	self.add_section_field		= function () { return self.create_blank_item (16); };
	self.add_quiz_field			= function () { return self.create_blank_item (17); };
	self.add_upload_field		= function () { return self.create_blank_item (18); };

	/**
	 * Put the last item into focus.
	 */
	self.focus_last_item = function () {
		var last_item = $('#item-list .item:last');

		$('html, body').animate ({
			scrollTop: last_item.offset ().top
		}, 500);

		last_item.find ('input:first').focus ();
	};

	/**
	 * Disable "Done Editing" link and show "Saving..." message.
	 */
	self.show_saving = function () {
		$('#saving').fadeIn ('slow');
		$('#done-editing').addClass ('disabled').on ('click', function (e) {
			e.preventDefault ();
			if ($(this).hasClass ('disabled')) {
				return false;
			}
			window.location.href = $(this).attr ('href');
		});
	};

	/**
	 * Re-enable "Done Editing" link and hide "Saving..." message.
	 */
	self.done_saving = function () {
		$('#done-editing').removeClass ('disabled');
		$('#saving').fadeOut ('slow');
	};

	/**
	 * Show the list tab.
	 */
	self.show_list = function () {
		$('#toggle-full').removeClass ('active');
		$('#toggle-list').addClass ('active');
		$('#toggle-preview').removeClass ('active');
		$('#item-list-full').hide ();
		$('#item-list-list').show ();
		$('#item-preview').hide ();
		return false;
	};

	/**
	 * Show the full tab.
	 */
	self.show_full = function () {
		$('#toggle-full').addClass ('active');
		$('#toggle-list').removeClass ('active');
		$('#toggle-preview').removeClass ('active');
		$('#item-list-full').show ();
		$('#item-list-list').hide ();
		$('#item-preview').hide ();

		// init/re-init plugins
		self.initialize_plugins ();
		return false;
	};
	
	return self;
})(jQuery);
