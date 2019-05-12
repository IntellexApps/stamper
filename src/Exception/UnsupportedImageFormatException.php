<?php namespace Intellex\ImageStamp\Exception;

use Intellex\Filesystem\File;

/**
 * Class UnsupportedImageFormatException indicates that a supplied file cannot be used for an image
 * as it is not in a supported format.
 *
 * @package Intellex\ImageStamp
 */
class UnsupportedImageFormatException extends Exception {

	/**
	 * UnsupportedImageFormatException constructor.
	 *
	 * @param                string $mime The mime type that was recognized in this file.
	 * @param string|string[]|File  $path The path that cannot be parsed.
	 */
	public function __construct($mime, $path) {
		parent::__construct("Unsupported format '{$mime}', in: " . print_r($path, true));
	}

}
