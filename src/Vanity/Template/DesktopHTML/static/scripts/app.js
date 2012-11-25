$(function() {

	prettyPrint();
	$('#nav-list').scrollspy();
	$('#bottom-spacer').height($(window).height());

	var top = $('#navTracker').offset().top - 77;

	$(window).scroll(function(event) {

		// what the y position of the scroll is
		var y = $(this).scrollTop();

		// whether that's below the form
		if (y >= top) {

			// if so, ad the fixed class
			$('#navTracker').addClass('fixed');
		}
		else {

			// otherwise remove it
			$('#navTracker').removeClass('fixed');
		}
	});

	$('#navTracker a').click(function(evt) {
		evt.preventDefault();

		var $node = $(this).attr('href'),
		    scrollTo = $($node).offset().top - 62;

		if ($.browser.webkit) {
			$('body').stop().animate({
				scrollTop: scrollTo
			}, 600, 'easeInOutCubic');
		}
		else {
			$('html').stop().animate({
				scrollTop: scrollTo
			}, 600, 'easeInOutExpo');
		}

		history.pushState({}, null, $node);
	});

	$('input.search-query').typeahead({
		items: 15,
		minLength: 2,
		source: function(query, process) {
			process($.grep(VANITY.TYPEAHEAD, function(a) {
				return a.match(new RegExp(query, 'gi'));
			}));
		},
		updater: function(item) {
			var path = item.replace(/\\/g, '/');

			if (path.indexOf('::') !== -1) {
				path = path.replace(/::/g, '/').replace(/\(\)/g, '');
				document.location = "{{ vanity.link.api_reference }}/" + path + '.html';
			}
			else {
				document.location = "{{ vanity.link.api_reference }}/" + path + '/index.html';
			}

			return item;
		}
	});
});
