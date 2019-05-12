<?php namespace Intellex\ImageStamp\Exception;

use Intellex\Filesystem\File;

/**
 * Class ImageCannotBeInitializedException indicates that a supplied file cannot be used,
 *
 * @package Intellex\ImageStamp
 */
class ImageCannotBeInitializedException extends Exception {

	/**
	 * ImageCannotBeInitializedException constructor.
	 *
	 * @param string|string[]|File $path The path that cannot be parsed.
	 */
	public function __construct($path) {
		parent::__construct('Unable to parse file as Image for ImageStamp: ' . print_r($path, true));
	}

}
