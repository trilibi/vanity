<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Home</title>

		<link rel="stylesheet" href="twitter-bootstrap/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="styles/app.css">
		<link rel="stylesheet" href="twitter-bootstrap/bootstrap/css/bootstrap-responsive.min.css">
		<link rel="stylesheet" href="packages/prettify/prettify.css">
	</head>

	<body data-spy="scroll" data-target="#navTracker" data-offset="100">

		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">

					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>

					<a href="" class="brand">AWS SDK for PHP</a>
 					<div class="nav-collapse">
						<ul class="nav">
							<li class="active"><a href="#">API Reference</a></li>
							<li><a href="#">User Guides</a></li>
							<li><a href="#">Screencasts</a></li>
							<li><a href="#">Examples</a></li>
						</ul>

						<form class="navbar-search pull-right">
							<input type="text" class="search-query" placeholder="Search">
						</form>
 					</div>

				</div>
			</div>
		</div>

		<div id="description" class="container">
			<div class="row">
				<div class="span8 offset2">
					<div class="hero-unit">
						<h1 style="text-align: center;">AWS SDK for PHP</h1>
						<p style="margin: 0; padding: 0.75em 0 1.5em 0; text-align: center;">Start developing apps on top of <strong>Amazon Web Services</strong> today.<br>Tap into the cost-effective, scalable, and reliable AWS cloud using our<br>Software Development Kit for PHP 5.3.</p>
						<p style="text-align: center;"><a class="btn btn-primary btn-large" href="" style="display: inline-block;">That sounds awesome. Let's get started!</a></p>
					</div>
				</div>
			</div>
		</div>

		<p class="footnote" align="center">Copyright &copy; 2010&ndash;2012 Amazon Web Services, LLC</p>
		<div id="bottom-spacer"></div>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="https://raw.github.com/danro/jquery-easing/master/jquery.easing.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="https://raw.github.com/skyzyx/dombuilder/master/dombuilder.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="twitter-bootstrap/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="packages/prettify/prettify.js" type="text/javascript" charset="utf-8"></script>

		<script type="text/javascript">

		$(function() {

			prettyPrint();
			$('#bottom-spacer').height($(window).height());
			$('#nav-list').scrollspy();
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
		});

		</script>
	</body>
</html>

