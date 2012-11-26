<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
 * Copyright (c) 2011-2012 [Amazon Web Services, Inc.](http://aws.amazon.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * <http://www.opensource.org/licenses/mit-license.php>
 */


namespace Vanity\Parse;

use Doctrine\Common\Cache\PhpFileCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Http\Client;
use Guzzle\Plugin\Backoff\BackoffPlugin;
use Guzzle\Plugin\Cache\CachePlugin;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Access the GitHub API for the source code.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class GitHub
{
	/**
	 * Stores the Guzzle client for the request.
	 * @type Client
	 */
	public $client;

	/**
	 * Stores the owner of the repository.
	 * @type string
	 */
	public $owner;

	/**
	 * Stores the name of the repository.
	 * @type string
	 */
	public $repository;

	/**
	 * Stores the GitHub username.
	 * @type string
	 */
	public $user;

	/**
	 * Stores the GitHub password.
	 * @type string
	 */
	public $pass;

	/**
	 * Constructs a new instance of the {@see GitHub} class.
	 *
	 * @param string $user Your GitHub username.
	 * @param string $pass Your GitHub password.
	 */
	public function __construct($user, $pass)
	{
		$this->user = $user;
		$this->pass = $pass;

		$this->client = new Client('https://api.github.com', array(
			'params.cache.override_ttl' => 3600,
		));

		// Caching
		$this->client->addSubscriber(
			new CachePlugin(
				new DoctrineCacheAdapter(
					new PhpFileCache(VANITY_CACHE_DIR . '/github')
				)
			, true)
		);

		// Exponential backoff
		$this->client->addSubscriber(BackoffPlugin::getExponentialBackoff());
	}

	/**
	 * Sets the repository to use for requests.
	 *
	 * @param string $owner      The owner of the GitHub repository.
	 * @param string $repository The name of the GitHub repository.
	 */
	public function setRepository($owner, $repository)
	{
		$this->owner = $owner;
		$this->repository = $repository;

		return $this;
	}

	/**
	 * Get the latest commit ID in the local repository.
	 *
	 * This has not yet been tested on Windows. YMMV.
	 *
	 * @return string The SHA hash from the latest commit.
	 */
	public function getLatestCommit()
	{
		$process = new Process('git log --pretty=format:%H -n 1');
		$process->setTimeout(5);
		$process->run();

		if (!$process->isSuccessful())
		{
		    throw new RuntimeException($process->getErrorOutput());
		}

		return trim($process->getOutput());
	}

	/**
	 * Get the GitHub authors associated with a specific file in the repository.
	 *
	 * @param  string $path The repository path to the file.
	 * @return array        An array containing pairs of GitHub users and their avatar URLs.
	 */
	public function getAuthorsForFile($path)
	{
		$request = $this->client->get('/repos/' . $this->owner . '/' .
			$this->repository . '/commits' . '?per_page=100&path=' . $path)
			->setAuth($this->user, $this->pass);

		$response = $request->send();

		$output = array();

		foreach (json_decode($response->getBody(), true) as $commit)
		{
			$output[$commit['author']['login']] = array(
				'avatar' => $commit['author']['avatar_url'],
			);
		}

		return $output;
	}
}
