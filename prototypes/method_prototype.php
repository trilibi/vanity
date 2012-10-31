<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Method</title>

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
							<input type="text" class="search-query" placeholder="Search" data-provide="typeahead">
						</form>
 					</div>

				</div>
			</div>
		</div>

		<div id="description" class="container">
			<div class="row">
				<div class="span12">
					<ul class="breadcrumb">
						<li><a href="#">AWS</a> <span class="divider">\</span></li>
						<li><a href="#">Common</a> <span class="divider">\</span></li>
						<li><a href="#">Generic</a> <span class="divider">\</span></li>
						<li><a href="#">Collection</a> <span class="divider">::</span></li>
						<li class="active">run_instances()</li>
					</ul>
				</div>
			</div>

			<div class="row">

				<div class="navigation-wrapper">
					<div class="span3 offset9 navigation" id="navTracker">

						<ul class="nav nav-list well">
							<li class="nav-header">Contents</li>
							<li><a href="#description"><i class="icon-book"></i> Description</a></li>
							<li><a href="#examples"><i class="icon-cog"></i> Examples</a></li>
							<li><a href="#parameters"><i class="icon-th-list"></i> Parameters</a></li>
							<li><a href="#returns"><i class="icon-share-alt"></i> Returns</a></li>
							<li><a href="#source"><i class="icon-pencil"></i> Source</a></li>
						</ul>

					</div>
				</div>

				<div class="span9 content">

					<div class="page-header">
						<h1>run_instances ( $options = array() )</h1>
					</div>

					<p>The <code>RunInstances</code> operation launches a specified number of instances.</p>
					<p>If Amazon EC2 cannot launch the minimum number AMIs you request, no instances launch. If there is insufficient capacity to launch the maximum number of AMIs you request, Amazon EC2 launches as many as possible to satisfy the requested maximum values.</p>
					<p>Every instance is launched in a security group. If you do not specify a security group at launch, the instances start in your default security group. For more information on creating security groups, see <a href="">CreateSecurityGroup</a>.</p>
					<p>An optional instance type can be specified. For information about instance types, see <a href="">Instance Types</a>.</p>
					<p>You can provide an optional key pair ID for each image in the launch request (for more information, see CreateKeyPair). All instances that are created from images that use this key pair will have access to the associated public key at boot. You can use this key to provide secure access to an instance of an image on a per-instance basis. Amazon EC2 public images use this feature to provide secure access without passwords.</p>
					<div class="alert alert-error well">
						<h4 class="alert-heading">Important!</h4>
						Launching public images without a key pair ID will leave them inaccessible.
					</div>
					<p>The public key material is made available to the instance at boot time by placing it in the <code>openssh_id.pub</code> file on a logical device that is exposed to the instance as <code>/dev/sda2</code> (the ephemeral store). The format of this file is suitable for use as an entry within <code>~/.ssh/authorized_keys</code> (the OpenSSH format). This can be done at boot (e.g., as part of <code>rc.local</code>) allowing for secure access without passwords.</p>
					<p>Optional user data can be provided in the launch request. All instances that collectively comprise the launch request have access to this data For more information, see Instance Metadata.</p>
					<div class="alert alert-info well">
						<h4 class="alert-heading">Note!</h4>
						If any of the AMIs have a product code attached for which the user has not subscribed, the RunInstances call will fail.
					</div>
					<p>We strongly recommend using the 2.6.18 Xen stock kernel with the <code>c1.medium</code> and <code>c1.xlarge</code> instances. Although the default Amazon EC2 kernels will work, the new kernels provide greater stability and performance for these instance types. For more information about kernels, see Kernels, RAM Disks, and Block Device Mappings.</p>

					<h2 id="examples">Examples</h2>
					<div class="example description">
						<h3><p>Launch an EC2 instance.</p></h3>
						<pre class="prettyprint linenums">// Instantiate the class
$ec2 = new AmazonEC2();

// Boot an instance of the image
$response = $ec2-&gt;run_instances('ami-84db39ed', 1, 1, array(
    'InstanceType' =&gt; 'm1.small'
));

// Success?
var_dump($response-&gt;isOK());</pre>
					</div>

					<h2 id="parameters">Parameters</h2>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped">
						<thead>
							<tr>
								<th><p>Name</p></th>
								<th class="description"><p>Description</p></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<p><code>ImageId</code></p>
								</td>
								<td class="description">
									<p><code><a href="">string</a></code> <small>Required</small></p>
									<p>Unique ID of a machine image, returned by a call to <code>describe_images()</code>.</p>
								</td>
							</tr>
							<tr>
								<td>
									<p><code>KeyName</code></p>
								</td>
								<td class="description">
									<p><code><a href="">string</a></code> <small>Required</small></p>
									<p>The name of the key pair.</p>
								</td>
							</tr>

							<tr>
								<td>
									<p><code>InstanceType</code></p>
								</td>
								<td class="description">
									<p><code><a href="">AWS\EC2\Enum\InstanceType</a></code> <small>Required</small></p>
									<p>Specifies the instance type for the launched instances.</p>
									<p>Allowed values: <code>t1.micro</code>, <code>m1.small</code>, <code>m1.large</code>, <code>m1.xlarge</code>, <code>m2.xlarge</code>, <code>m2.2xlarge</code>, <code>m2.4xlarge</code>, <code>c1.medium</code>, <code>c1.xlarge</code>, <code>cc1.4xlarge</code>, <code>cc2.8xlarge</code>, <code>cg1.4xlarge</code></p>
								</td>
							</tr>
							<tr>
								<td>
									<p><code>BlockDeviceMapping</code></p>
								</td>
								<td class="description">
									<p><code><a href="">array</a></code></p>
									<p>Specifies how block devices are exposed to the instance. Each mapping is made up of a <code>virtualName</code> and a <code>deviceName</code>.</p>
									<p>Supports one or more sets of the following options:</p>

									<p>
										<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped">
											<tr>
												<td>
													<p><code>KeyName</code></p>
												</td>
												<td class="description">
													<p><code><a href="http://php.net/string">string</a></code></p>
													<p>The name of the key pair.</p>
													<p><em><small>The default value is NULL.</small></em></p>
												</td>
											</tr>
											<tr>
												<td>
													<p><code>VirtualName</code></p>
												</td>
												<td class="description">
													<p><code><a href="http://php.net/string">string</a></code></p>
													<p>Specifies the virtual device name.</p>
													<p><em><small>The default value is NULL.</small></em></p>
												</td>
											</tr>
											<tr>
												<td>
													<p><code>DeviceName</code></p>
												</td>
												<td class="description">
													<p><code><a href="http://php.net/string">string</a></code></p>
													<p>Specifies the device name (e.g., <code>/dev/sdh</code>).</p>
													<p><em><small>The default value is NULL.</small></em></p>
												</td>
											</tr>
											<tr>
												<td>
													<p><code>Ebs</code></p>
												</td>
												<td class="description">
													<p><code><a href="http://php.net/string">string</a></code></p>
													<p>Specifies parameters used to automatically setup Amazon EBS volumes when the instance is launched.</p>
													<p>Supports one or more sets of the following options:</p>

													<p>
														<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped">
															<tr>
																<td>
																	<p><code>SnapshotId</code></p>
																</td>
																<td class="description">
																	<p><code><a href="http://php.net/string">string</a></code></p>
																	<p>The ID of the snapshot from which the volume will be created.</p>
																	<p><em><small>The default value is NULL.</small></em></p>
																</td>
															</tr>
															<tr>
																<td>
																	<p><code>VolumeSize</code></p>
																</td>
																<td class="description">
																	<p><code><a href="http://php.net/integer">integer</a></code></p>
																	<p>The size of the volume, in gigabytes.</p>
																	<p><em><small>The default value is 5.</small></em></p>
																</td>
															</tr>
															<tr>
																<td>
																	<p><code>DeleteOnTermination</code></p>
																</td>
																<td class="description">
																	<p><code><a href="http://php.net/boolean">boolean</a></code></p>
																	<p>Specifies whether the Amazon EBS volume is deleted on instance termination.</p>
																	<p><em><small>The default value is FALSE.</small></em></p>
																</td>
															</tr>
														</table>
													</p>
												</td>
											</tr>
											<tr>
												<td>
													<p><code>NoDevice</code></p>
												</td>
												<td class="description">
													<p><code><a href="http://php.net/string">string</a></code></p>
													<p>Specifies the device name to suppress during instance launch.</p>
													<p><em><small>The default value is NULL.</small></em></p>
												</td>
											</tr>
										</table>
									</p>
								</td>
							</tr>
						</tbody>
					</table>

					<h2 id="returns">Returns</h2>
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped">
						<thead>
							<tr>
								<th><p>Type</p></th>
								<th class="description"><p>Description</p></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<p><code><a href="">CFResponse</a></code></p>
								</td>
								<td class="description">
									<p>A <code><a href="">CFResponse</a></code> object containing a parsed HTTP response.</p>
								</td>
							</tr>
						</tbody>
					</table>

					<h2 id="source">Source</h2>
					<p>Method defined in <a href="https://github.com/amazonwebservices/aws-sdk-for-php/blob/master/services/ec2.class.php#L4091">services/ec2.class.php</a>
					<pre class="prettyprint linenums">public function run_instances($image_id, $min_count, $max_count, $opt = null)
{
    if (!$opt) $opt = array();
    $opt['ImageId'] = $image_id;
    $opt['MinCount'] = $min_count;
    $opt['MaxCount'] = $max_count;

    // Optional list (non-map)
    if (isset($opt['SecurityGroup']))
    {
        $opt = array_merge($opt, CFComplexType::map(array(
            'SecurityGroup' =&gt; (is_array($opt['SecurityGroup']) ? $opt['SecurityGroup'] : array($opt['SecurityGroup']))
        )));
        unset($opt['SecurityGroup']);
    }

    // Optional list (non-map)
    if (isset($opt['SecurityGroupId']))
    {
        $opt = array_merge($opt, CFComplexType::map(array(
            'SecurityGroupId' =&gt; (is_array($opt['SecurityGroupId']) ? $opt['SecurityGroupId'] : array($opt['SecurityGroupId']))
        )));
        unset($opt['SecurityGroupId']);
    }

    // Optional map (non-list)
    if (isset($opt['Placement']))
    {
        $opt = array_merge($opt, CFComplexType::map(array(
            'Placement' =&gt; $opt['Placement']
        )));
        unset($opt['Placement']);
    }

    // Optional list + map
    if (isset($opt['BlockDeviceMapping']))
    {
        $opt = array_merge($opt, CFComplexType::map(array(
            'BlockDeviceMapping' =&gt; $opt['BlockDeviceMapping']
        )));
        unset($opt['BlockDeviceMapping']);
    }

    // Optional map (non-list)
    if (isset($opt['License']))
    {
        $opt = array_merge($opt, CFComplexType::map(array(
            'License' =&gt; $opt['License']
        )));
        unset($opt['License']);
    }

    return $this-&gt;authenticate('RunInstances', $opt);
}</pre>

				</div>
			</div>
		</div>

		<p class="footnote" align="center">Copyright &copy; 2010&ndash;2012 Amazon Web Services, Inc.</p>
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

			$('.search-query').typeahead({
				'source': [
					'User Guide &rarr; Concepts &rarr; About Regions',
					'AWS\\EC2\\Enums\\InstanceType::MICRO',
					'AWS\\EC2\\Enums\\InstanceType::SMALL',
					'AWS\\EC2\\Enums\\InstanceType::LARGE',
					'AWS\\EC2\\Enums\\Region::VIRGINIA',
					'AWS\\EC2\\Enums\\Region::CALIFORNIA',
					'AWS\\EC2\\Enums\\Region::OREGON',
					'AWS\\EC2\\Enums\\Region::IRELAND',
					'AWS\\EC2\\Enums\\Region::SAO_PAULO',
					'AWS\\EC2\\Enums\\Region::TOKYO',
					'AWS\\EC2\\Enums\\Region::SINGAPORE',
				]
			});
		});

		</script>
	</body>
</html>

