<?php namespace Intellex\ImageStamp;

use Intellex\Filesystem\File;
use Intellex\ImageStamp\Exception\UnsupportedStampFormatException;

/**
 * Class Stamp represents a single stamp that will be placed on the image.
 *
 * @package Intellex\ImageStamp
 */
class Stamp {

	/** @var Image The absolute path to the image. */
	private $source;

	/**
	 * Stamp constructor.
	 *
	 * @param string|string[]|File|Image $source         The path to the image.
	 * @param Transformation|null        $transformation The optional transformation to apply on
	 *                                                   the image.
	 *
	 * @throws Exception\ImageCannotBeInitializedException
	 * @throws Exception\UnsupportedImageFormatException
	 * @throws UnsupportedStampFormatException
	 * @throws \Intellex\Filesystem\Exception\NotAFileException
	 * @throws \Intellex\Filesystem\Exception\PathNotReadableException
	 */
	public function __construct($source, $transformation = null) {
		$this->source = Image::fromFile($source, $transformation);
		if ($this->source->getType() !== Image::TYPE_PNG) {
			throw new UnsupportedStampFormatException();
		}
	}

	/** @return Image The image that is the stamp. */
	public function getSource() {
		return $this->source;
	}

}
