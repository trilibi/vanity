<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>User Guide</title>

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

						<ul class="nav nav-list well" id="nav-list">
							<li class="nav-header">Contents</li>
							<li><a href="#description">Signing up for Amazon Web Services</a></li>
							<li><a href="#getting_your_aws_security_credentials">Getting your AWS Security Credentials</a></li>
						</ul>

					</div>
				</div>

				<div class="span9 content">

					<div class="page-header">
						<h1>Signing up for Amazon Web Services</h1>
					</div>

					<!--*************************************************************************************************-->

					<p>Before you can begin using Amazon Web Services, you must sign up for each service you want to use.</p>

					<ol>
						<li>
							<p>Go to the home page for the service and click the <em>Sign Up</em> button on the upper-right corner of the page. A list of services and their descriptions can be found at <a href="http://aws.amazon.com/products">aws.amazon.com/products</a>.</p>
							<div class="screenshot">
								<img src="figures/signup_detail_page.png" alt="Detail page for Amazon S3.">
							</div>
						</li>
						<li>
							<p>You will be asked to sign into your AWS account. If you don't already have one, you will be prompted to create one as part of the sign up process.</p>
							<div class="screenshot">
								<img src="figures/signup_signin.png" alt="Signing into AWS.">
							</div>
						</li>
						<li>
							<p>Follow the on-screen instructions, ending with completing the signup.</p>
							<div class="screenshot">
								<img src="figures/signup_complete.png" alt="Completing the Amazon S3 signup process.">
							</div>
						</li>
						<li>
							<p>AWS sends you a confirmation e-mail after the sign-up process is complete. At any time, you can view your current account activity and manage your account by going to <a href="http://aws.amazon.com">aws.amazon.com</a> and clicking <em>Your Account</em>.</p>
						</li>
					</ol>

					<div class="alert alert-info">
						<h4 class="alert-heading">Remember!</h4>
						<p>Your keys are important. Your keys are valuable. They are the <em>keys to the kingdom</em>. Everywhere you look, there are vandals, thieves and shifty eyes as far as the eye can see. These vandals, thieves and shifty-eyed folks want nothing more than to steal your keys, rack up a bunch of charges on your AWS account, and put Baby in the corner. Nobody puts Baby in the corner.</p>
						<p><strong>Don't let this happen to you!</strong> Protect your keys at all costs!</p>
					</div>

					<h2 id="getting_your_aws_security_credentials">Getting your AWS Security Credentials</h2>
					<p>AWS uses several different kinds of credentials for various features; the most important of which are the <strong>Key</strong> and <strong>Secret Key</strong>. At a minimum, you'll need these two credentials for everything in the SDK.</p>
					<p>The SDK ships with a configuration file called <code>config-sample.inc.php</code> which is intended to store your various credentials as constants for use with the SDK. Rename <code>config-sample.inc.php</code> to <code>config.inc.php</code>, then open the file for editing.</p>

					<ol>
						<li>
							<p>In the main toolbar, click <em>Account</em> &rarr; <em>Security Credentials</em>.</p>
							<div class="screenshot">
								<img src="figures/signup_get_keys.png" alt="Finding your security credentials.">
							</div>
						</li>
						<li>
							<p>This is the Security Credentials page.</p>
							<div class="screenshot">
								<img src="figures/signup_security_credentials.png" alt="The Security Credentials page.">
							</div>
						</li>
						<li>
							<p>Scroll down the page. Find the key and secret key, and set them as the values for the <code>AWS_KEY</code> and <code>AWS_SECRET_KEY</code> constants in the <code>config.inc.php</code> file.</p>
							<div class="screenshot">
								<img src="figures/signup_key_secret.png" alt="The Key and Secret Key.">
							</div>
						</li>
						<li>
							<p><strong>(Optional)</strong> CloudFront has a feature which allows you to sign private URLs. This feature requires a keypair and its corresponding private key (<code>.pem</code>) file. Clicking on <em>Create a New Key Pair</em> will create a new keypair, then immediately begin downloading a the corresponding private key.</p>
							<div class="alert alert-error well">
								<h4 class="alert-heading">Important!</h4>
								<strong>Don't lose the private key!</strong> Amazon does not keep a copy of the private key. This means that if you lose it, there is no way to retrieve a copy.
							</div>
							<p>Add the keypair ID to the <code>AWS_CLOUDFRONT_KEYPAIR_ID</code> constant, and the <em>contents</em> of the private key in the <code>AWS_CLOUDFRONT_PRIVATE_KEY_PEM</code> constant.</p>
							<div class="screenshot">
								<img src="figures/signup_cloudfront_ec2_keypairs.png" alt="Getting CloudFront and EC2 keypairs.">
							</div>
						</li>
						<li>
							<p><strong>(Optional)</strong> Amazon has a feature called <em>AWS Multi-Factor Authentication</em>. This is an optional credential that allows you to increase the level of your account security. To add this security option to your account, you will need to purchase a compatible authentication device from Gemalto, a third-party provider.</p>
							<p>To save yourself some typing, you can add the serial number for the Gemalto device to the <code>AWS_MFA_SERIAL</code> constant in the <code>config.inc.php</code> file.</p>
							<div class="screenshot">
								<img src="figures/signup_gemalto.png" alt="Enable AWS Multi-Factor Authentication.">
							</div>
						</li>
						<li>
							<p>The AWS account and canonical IDs are used in Amazon EC2 and Amazon S3, respectively. Add the AWS account ID to the <code>AWS_ACCOUNT_ID</code> constant, and the AWS canonical ID to the <code>AWS_CANONICAL_ID</code> constant in the <code>config.inc.php</code> file.</p>
							<div class="screenshot">
								<img src="figures/signup_account_id.png" alt="AWS Account and Canonical IDs.">
							</div>
						</li>
						<li>
							<p>The last piece of information that the configuration file needs is the <code>AWS_CANONICAL_NAME</code> constant. Unfortunately, this isn't as easy to find as the other pieces of information, but you can get it with the SDK.</p>
							<p>We're jumping a little ahead of ourselves with this, but if you already know how to use the SDK, you can do the following.</p>
							<pre class="prettyprint linenums">$s3 = new AmazonS3();
print_r($s3-&gt;get_canonical_id());</pre>
							<p>If you don't quite know what this code means, that's OK. We can tackle this later.</p>
						</li>
					</ol>

					<!--*************************************************************************************************-->

				</div>
			</div>
		</div>

		<p class="footnote" align="center">Copyright &copy; 2010&ndash;2012 Amazon Web Services, Inc.</p>
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

