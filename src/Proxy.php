<?php namespace Intellex\ImageStamp;

use Intellex\Debugger\IncidentHandler;
use Intellex\Filesystem\File;

/**
 * Class Proxy is used to intercept a request and return something else.
 *
 * @package Intellex\ImageStamp
 */
abstract class Proxy {

	/** @var string The original requested path. */
	private $requestPath = [];

	/** @var string[] The GET parameters from the URL */
	private $requestParameters = [];

	/**
	 * Proxy constructor.
	 */
	public function __construct() {

		// Load basic info
		$this->requestParameters = $_GET;
		if (strstr($_SERVER['REQUEST_URI'], '?')) {
			$this->requestPath = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
		} else {
			$this->requestPath = $_SERVER['REQUEST_URI'];
		}
		$this->requestPath = urldecode($this->requestPath);

		// Try cache
		$this->tryCache();
		try {

			// Handle
			$response = $this->handle();
			if ($response) {

				// Save to cache
				if ($cacheFile = $this->defineCache()) {
					try {
						$cacheFile->write($response);

					} catch (\Exception $exception) {
						$this->isDebug() and IncidentHandler::handleException($exception);
					}
				}

				// Send response
				echo $response;

			} else {
				http_response_code(404);
			}

		} catch (\Exception $exception) {
			$this->isDebug() and IncidentHandler::handleException($exception);
			http_response_code(404);
		}

		exit(0);
	}

	/**
	 * Try to serve the response from the Cache.
	 */
	protected function tryCache() {
		try {
			$cacheFile = $this->defineCache();

			// If cache is found and valid send the response and exit
			if ($cacheFile && $cacheFile->isReadable() && $cacheFile->getLastModifiedTime() > time() - $this->defineCacheTimeToLive()) {
				header('Content-Type: ' . $cacheFile->getMimetype());
				readfile($cacheFile->getPath());
				exit(0);
			}

		} catch (\Exception $exception) {
			$this->isDebug() and IncidentHandler::handleException($exception);
		}
	}

	/** @return string The original requested path. */
	public function getRequestPath() {
		return $this->requestPath;
	}

	/** @return string[] The GET parameters from the URL */
	public function getRequestParameters() {
		return $this->requestParameters;
	}

	/**
	 * Get a single GET parameter by its name.
	 *
	 * @param string $name The name of the GET parameter.
	 *
	 * @return string The GET parameter, or null if it does not exist.
	 */
	public function getRequestParameter($name) {
		return key_exists($name, $this->requestParameters)
			? $this->requestParameters[$name]
			: null;
	}

	/**
	 * Set the debug version.
	 *
	 * @return bool True to enable debugging, false for production.
	 */
	protected function isDebug() {
		return false;
	}

	/**
	 * Define the File that will cache response for this request.
	 *
	 * @return File|null The file where the cache for this request will be stored, or null to
	 *                   completely bypass the cache.
	 */
	abstract protected function defineCache();

	/**
	 * Define for how long will response be cached.
	 *
	 * @return int The time in seconds for how long will the cache be considered valid.
	 */
	abstract protected function defineCacheTimeToLive();

	/**
	 * Handle the request.
	 *
	 * @return string The response to send to the requesting client.
	 * @throws \Exception
	 */
	abstract protected function handle();

}
