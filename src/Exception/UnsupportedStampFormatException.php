<?php namespace Intellex\ImageStamp\Exception;

/**
 * Class UnsupportedStampFormatException indicates that a supplied file cannot be used for a stamp
 * as it is not in a supported format.
 *
 * @package Intellex\ImageStamp
 */
class UnsupportedStampFormatException extends Exception {

	/**
	 * UnsupportedStampFormatException constructor.
	 */
	public function __construct() {
		parent::__construct('Only PNG files can be used for a stamp');
	}

}
