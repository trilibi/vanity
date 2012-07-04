<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>README</title>

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
				<div class="span12">
					<ul class="breadcrumb">
						<li class="active">README</li>
					</ul>
				</div>
			</div>

			<div class="row">

				<div class="navigation-wrapper">
					<div class="span3 offset9 navigation" id="navTracker">

						<ul class="nav nav-list well">
							<li class="nav-header">Contents</li>
							<li><a href="#description">Description</a></li>
							<li><a href="#installing">Installing</a></li>
							<li><a href="#support">Support</a></li>
							<li><a href="#installing_development_dependencies">Installing Development Dependencies</a></li>
							<li><a href="#running_tests_and_generating_reports">Running tests and generating reports</a></li>
							<li><a href="#contributing">Contributing</a></li>
							<li><a href="#to_the_clou_er_i_mean_code">To the clou—, er... I mean, code!</a></li>
						</ul>

					</div>
				</div>

				<div class="span9 content">

					<div class="page-header">
						<h1>AWS SDK for PHP</h1>
					</div>

<p>The PHP Developer Channel is where we experiment with new ideas for the AWS SDK for PHP. The best ideas make it into the officially-supported release channel. We encourage you to fork this repository, muck around with things, run through the test suite to make sure that nothing was broken, then submit a pull request.</p>

<h2 id="installing">Installing</h2>

<p>Depending on your needs, there are a few different ways you can install the AWS SDK for PHP. <em>(This isn't true for the dev-channel — only the release channel!)</em></p>

<h3>Install globally via PEAR</h3>

<p>To install the AWS SDK for PHP so that it is available globally in your PHP environment (you may need to use <code>sudo</code>):</p>

<pre class="prettyprint linenums lang-sh">pear channel-discover pear.amazonwebservices.com
pear install aws/sdk
</pre>

<p>And include it in your scripts (assuming that your <code>$PEAR</code> path is in your <code>$PATH</code>):</p>

<pre class="prettyprint linenums lang-php">require_once 'aws-sdk.phar';</pre>

<h3>Install locally in your project</h3>

<p>To install the AWS SDK for PHP so that it is bundled up as part of your local project:</p>

<pre class="prettyprint linenums lang-sh">wget --quiet http://pear.amazonwebservices.com/get/aws-sdk.phar</pre>

<p>And include it in your scripts:</p>

<pre class="prettyprint linenums lang-php">require_once '/path/to/aws-sdk.phar';</pre>

<h3>Bundling the SDK as a dependency (using Composer)</h3>

<p>To add the AWS SDK for PHP as a dependency in your <code>composer.json</code> file:</p>

<pre class="prettyprint linenums lang-json">{
    "require": {
        "aws/sdk": "&gt;=2.0"
    }
}
</pre>

<h2 id="support">Support</h2>

<p>Short version: <strong>There is none.</strong></p>

<h3>Zip, zilch, nada...</h3>

<p>There is absolutely no official support for anything in this repository (no forums, no paid AWS support, nothing...). This repository is for people who like to roll up their sleeves and dig in. Everything here should be considered <strong>alpha-quality</strong> code, and only a <em>fool</em> would use the code from this repository in a Production Environment.</p>

<h3>Bug reports and feature requests</h3>

<p>Bug reports and feature requests that come in the form of pull requests with 100% test coverage are fast-tracked to the front of the line. If you file a bug or request a feature without putting some code behind it, or only submit partially-tested code, these will wait at the end of the line. (All contributions MUST include tests with 100% code coverage to be considered for inclusion.)</p>

<h3>Release channel</h3>

<p>If you're looking for something stable and supported, stick to the <a href="https://github.com/amazonwebservices/aws-sdk-for-php">release channel</a>.</p>

<h2 id="installing_development_dependencies">Installing Development Dependencies</h2>

<p>In order to do anything useful with the raw source code (in terms of developing, coding, testing, etc.), you'll need some basic tools installed first. If you've been developing in PHP for any length of time, chances are you already have most (all?) of these installed.</p>

<h3>Xdebug</h3>

<p>This is a <em>PHP extension</em>. Install it from your system's package management system or <a href="http://xdebug.org/download.php">download it directly</a>.</p>

<h3>PEAR or Pyrus</h3>

<p>You'll need one of these for PHP package management. Most of the examples use PEAR.</p>

<ul>
<li><a href="http://pear.php.net/manual/en/installation.php">http://pear.php.net/manual/en/installation.php</a></li>
<li><a href="http://pear.php.net/manual/en/installationpyrus.php">http://pear.php.net/manual/en/installationpyrus.php</a></li>
</ul><h3>Composer</h3>

<p>Follow the instructions for installing globally. Make sure you rename <code>composer.phar</code> to simply <code>composer</code>.</p>

<ul>
<li><a href="https://github.com/composer/composer/blob/master/README.md">https://github.com/composer/composer/blob/master/README.md</a></li>
</ul><h3>Phing, PHPUnit and other tools</h3>

<pre class="prettyprint linenums lang-sh">pear config-set auto_discover 1
pear install --alldeps pear.phing.info/phing \
                       pear.phpunit.de/PHPUnit \
                       pear.phpmd.org/PHP_PMD \
                       pear.phpunit.de/phpcpd \
                       bartlett.laurent-laville.org/PHP_CompatInfo \
                       pear.behat.org/behat \
                       pear.domain51.com/Phing_d51PearPkg2Task
</pre>

<h3>Download source from GitHub</h3>

<p>To install the source code for the AWS SDK for PHP:</p>

<pre class="prettyprint linenums lang-sh">git clone git://github.com/skyzyx/sdk2-prototype.git
cd sdk2-prototype
phing composer
</pre>

<p>And include it in your scripts:</p>

<pre class="prettyprint linenums lang-php">require_once '/path/to/src/bootstrap.php';</pre>

<h2 id="running_tests_and_generating_reports">Running tests and generating reports</h2>

<p>Besides test coverage, we also spend time looking at reports generated by various tools. You can get a list of all available tasks by running:</p>

<pre class="prettyprint linenums lang-sh">phing</pre>

<p>All supported code analysis reports can be generated and written to <code>tests/analyze/</code> by calling:</p>

<pre class="prettyprint linenums lang-sh">phing analyze.all</pre>

<p>All tests can be run and testing reports can be generated and written to <code>tests/analyze/phpunit/</code> by calling:</p>

<pre class="prettyprint linenums lang-sh">phing test.all</pre>

<h2 id="contributing">Contributing</h2>

<p>To view the list of contributors, run the following command from the Terminal:</p>

<pre class="prettyprint linenums lang-sh">git shortlog -sne --no-merges</pre>

<h3>How?</h3>

<p>Here's the process for contributing:</p>

<ol>
<li>You write code, add tests with 100% code coverage.</li>
<li>You submit a GitHub pull request to us with a description of what the change is.</li>
<li>We take a look at the contribution and determine whether or not to pull it in.</li>
<li>If so, we ping the lawyers to take a look at the contribution and make sure that it won't trigger a lawsuit (or other legal nonsense) against Amazon. If it's particularly gnarly (IP-wise) the lawyers may request that you sign a Contributor Licensing Agreement (CLA).</li>
<li>If all goes well, we accept your pull request and your changes are merged in.</li>
<li>You will become "Internet famous" with anybody who runs <code>git shortlog</code> from the Terminal. :)</li>
</ol>

<h3>Licensing</h3>

<p>Our source code is <a href="http://aws.amazon.com/apache2.0/">Apache 2.0</a>-licensed. If you're willing to release your contribution under the same license, you can save a step and simply tell us so in the pull request's description. If you're adamant about using a different license, it needs to be compatible with Apache 2.0 (e.g., <a href="http://opensource.org/licenses/PHP-3.0">PHP</a>, <a href="http://opensource.org/licenses/MIT">MIT</a>, <a href="http://opensource.org/licenses/BSD-3-Clause">3-clause BSD</a> (1999), <a href="http://opensource.org/licenses/BSD-2-Clause">2-clause BSD</a> (2008)).</p>

<p><em>Any GPL/LGPL code will be rejected outright.</em></p>

<h2 id="to_the_clou_er_i_mean_code">To the clou—, er... I mean, <em>code!</em></h2>

<h3>Philosophy</h3>

<p>There are a few ideals that went behind our thinking:</p>

<ol>
<li><p>It's time to offer a professional-grade developer SDK. One that favors people who work with PHP rather than the n00bs.</p></li>
<li><p>Let's really embrace all of the goodness that PHP 5.3 allows. We don't use any of the whiz-bang features just for fun. Instead we use them (and encourage their patterns) because it makes us all more efficient and better developers.</p></li>
<li><p>PHP is not Java. If you want Java, then use Java. PHP is a dynamic language, and it's high time we all use it as such.</p></li>
<li><p>PHP's core library is crufty, while the newer additions are much better thought-out. Let's take advantage of the newer stuff to help us radically improve the crufty core.</p></li>
<li><p>Let's leverage the power of chainable objects and inheritance, and promote patterns that enable us to do more with less code.</p></li>
<li><p>There is no such thing as a singular "PHP community". PHP is more like the Germanic tribes of ancient Europe than it is like the Roman Empire. Let's look at how these tribes of PHP developers work, and see what we can do to streamline development for as many tribes as possible.</p></li>
</ol>

<h3>Fundamentals</h3>

<p>There are a few core constructs that very nearly every object in the dev-channel is built on top of.</p>

<ul>
<li>
<strong>Collections:</strong> Groups of data. Very similar in concept to arrays, but fully object-oriented and chainable.</li>
<li>
<strong>Resources:</strong> Individual, well, resources. (e.g., an S3 bucket, an EC2 instance, a DynamoDB table)</li>
<li>
<strong>Enums:</strong> Otherwise known as <em>enumerations</em>, these are essentially collections of Constants that are all grouped around a specific task. (e.g., region endpoints, available instance types)</li>
<li>
<strong>Events:</strong> Events are fired at various points through the execution of the code. We expose these events so that you can hook up your own functionality without needing to modify this package.</li>
</ul><p>We also have a number of other utility classes that are designed to save you from having to write a ton of code to do cool stuff.</p>

<p><em>(There are a small number of exceptions to rule, but we've tried to keep that list small. Wanna help us remove those exceptions? Upgrade to PHP 5.4 and allow us to use Traits. :) )</em></p>

				</div>
			</div>
		</div>

		<p class="footnote" align="center">Copyright &copy; 2010&ndash;2012 Amazon Web Services, LLC</p>
		<div id="bottom-spacer"></div>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="https://raw.github.com/danro/jquery-easing/master/jquery.easing.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="twitter-bootstrap/bootstrap/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="packages/prettify/prettify.js" type="text/javascript" charset="utf-8"></script>

		<script type="text/javascript">

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
		});

		</script>
	</body>
</html>

