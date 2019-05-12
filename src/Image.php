<?php namespace Intellex\ImageStamp;

use Intellex\Filesystem\File;

/**
 * Class Image represents the image data.
 *
 * @package Intellex\ImageStamp
 */
class Image {

	/** @var string Indicates that the image is of type JPEG. */
	const TYPE_JPEG = 'jpeg';

	/** @var string Indicates that the image is of type PNG. */
	const TYPE_PNG = 'png';

	/** @var resource $type The image data. */
	private $data;

	/** @var string $type The type of the image, either static::TYPE_JPEG or static::TYPE_PNG. */
	private $type;

	/**
	 * Image constructor.
	 *
	 * @param resource            $data                  The image data.
	 * @param string              $type                  The type of the image, either
	 *                                                   static::TYPE_JPEG or static::TYPE_PNG.
	 * @param Transformation|null $transformation        The optional transformation to apply on
	 *                                                   the image.
	 */
	public function __construct($data, $type, $transformation = null) {
		$this->data = $data;
		$this->type = $type;

		// Apply the transformation
		if ($transformation) {
			$this->apply($transformation);
		}

		imageAlphaBlending($this->data, true);
	}

	/**
	 * Parse a file to an image.
	 *
	 * @param string|string[]|File|Image $source         The path to the image.
	 * @param Transformation|null        $transformation The optional transformation to apply on
	 *                                                   the image.
	 *
	 * @return Image The parsed image.
	 * @throws \Intellex\Filesystem\Exception\NotAFileException
	 * @throws \Intellex\Filesystem\Exception\PathNotReadableException
	 * @throws Exception\UnsupportedImageFormatException
	 * @throws Exception\ImageCannotBeInitializedException
	 */
	public static function fromFile($source, $transformation = null) {

		// From file
		$source = static::assertFile($source);
		$path = $source->getPath();

		// Make sure the file is readable
		if (!$source->isReadable()) {
			throw new \Intellex\Filesystem\Exception\PathNotReadableException($source);
		}

		// Load based on the mime type
		$mimeType = $source->getMimetype();
		switch ($mimeType) {
			case 'image/jpeg':
				$type = static::TYPE_JPEG;
				$data = imageCreateFromJPEG($path);
				break;

			case 'image/png':
				$type = static::TYPE_PNG;
				$data = imageCreateFromPNG($path);
				break;

			default:
				throw new Exception\UnsupportedImageFormatException($mimeType, $path);
		}

		return new self($data, $type, $transformation);
	}

	/**
	 * Set the stamp on this image.
	 *
	 * @param Stamp $stamp The stamp to put.
	 * @param int   $top   The top coordinate of the stamp.
	 * @param int   $left  The left coordinate of the stamp.
	 */
	public function stamp($stamp, $top, $left) {
		$stampData = $stamp->getSource()->getData();

		// Stamp
		imageCopyResampled(
			$this->data, $stampData,
			$top, $left,
			0, 0,
			imageSX($stampData), imageSY($stampData),
			imageSX($stampData), imageSY($stampData)
		);

		// Clean up
		imageDestroy($stampData);
	}

	/**
	 * Set the tiled watermark on this image.
	 *
	 * @param Stamp $stamp The stamp to put.
	 */
	public function watermark($stamp) {
		$stampData = $stamp->getSource()->getData();

		// Create the tile and overlay
		imageSaveAlpha($stampData, true);
		imageAlphaBlending($stampData, false);
		imageSetTile($this->data, $stampData);
		imageFilledRectangle($this->data, 0, 0, imagesx($this->data), imagesx($this->data), IMG_COLOR_TILED);

		// Clean up
		imageDestroy($stampData);
	}

	/**
	 * Apply the transformation.
	 *
	 * @param Transformation $transformation The transformation to apply on the image.
	 */
	public function apply($transformation) {
		$this->data = $this->resize($transformation->getWidth(), $transformation->getHeight());
		$this->data = $this->rotate($transformation->getRotation());
		$this->data = $this->setOpacity($transformation->getOpacity());
	}

	/**
	 * Resize an image resource.
	 *
	 * @param int $width  The new width of the image.
	 * @param int $height The new height of the image.
	 *
	 * @return resource The image data in the new size
	 */
	public function resize($width, $height) {
		$newData = $this->data;
		if ($width != null && $width !== null && $width != imageSX($this->data) && $height != imageSY($this->data)) {

			// Resize the source
			$newData = imagecreatetruecolor($width, $height);
			imageSaveAlpha($newData, true);
			imageAlphaBlending($newData, false);
			imageCopyResampled(
				$newData, $this->data,
				0, 0,
				0, 0,
				$width, $height,
				imageSX($this->data), imageSY($this->data)
			);
		}

		return $this->data = $newData;
	}

	/**
	 * Rotate an image resource.
	 *
	 * @param int $degrees The amount in which to rotate the image anti-clockwise, in degrees.
	 *
	 * @return resource The rotated image resource.
	 */
	public function rotate($degrees) {
		if ($degrees !== null && $degrees !== 0) {
			$this->data = imageRotate($this->data, $degrees, imageColorAllocateAlpha($this->data, 0, 0, 0, 127));
		}

		return $this->data;
	}

	/**
	 * Set or opacity for an image resource.
	 *
	 * @param float $opacity The opacity of the stamp, as a value between 0.0 and 1.0.
	 *
	 * @return resource The modified image resource.
	 */
	public function setOpacity($opacity) {
		if ($opacity !== null && $opacity < 1) {

			// Get the size of the image
			$width = imageSX($this->data);
			$height = imageSY($this->data);

			// Set opacity for each pixel
			for ($x = 0; $x < $width; $x++) {
				for ($y = 0; $y < $height; $y++) {
					$pixel = imageColorAt($this->data, $x, $y);
					$alpha = ($pixel >> 24) & 0xFF;
					$alpha = 127 - max(0, min(127, round($opacity * (127 - $alpha))));
					$pixel = imageColorAllocateAlpha($this->data, ($pixel >> 16) & 0xFF, ($pixel >> 8) & 0xFF, $pixel & 0xFF, $alpha);
					imageSetPixel($this->data, $x, $y, $pixel);
				}
			}
		}

		return $this->data;
	}

	/**
	 * Write the contents to a file.
	 *
	 * @param string|string[]|File $file The file where to write the image.
	 *
	 * @return File The file where the data is written.
	 * @throws \Intellex\Filesystem\Exception\NotADirectoryException
	 * @throws \Intellex\Filesystem\Exception\NotAFileException
	 * @throws \Intellex\Filesystem\Exception\PathNotWritableException
	 * @throws Exception\ImageCannotBeInitializedException
	 */
	public function writeToFile($file) {
		$file = static::assertFile($file);

		// Capture the image
		ob_start();
		switch ($this->getType()) {
			case static::TYPE_JPEG:
				imageJPEG($this->data);
				break;

			case static::TYPE_PNG:
				imageSaveAlpha($this->data, true);
				imageAlphaBlending($this->data, false);
				imagePNG($this->data);
				break;
		}
		$data = ob_get_clean();

		// Save to file
		$file->write($data);
		return $file;
	}

	/**
	 * Assert that the input is a File.
	 *
	 * @param string|string[]|File $path The raw input.
	 *
	 * @return File The instance of File.
	 * @throws Exception\ImageCannotBeInitializedException
	 */
	private static function assertFile($path) {

		// From string or string array
		if (is_string($path) || is_array($path)) {
			$path = new File($path);
		}

		// Assert that this is a File
		if (!($path instanceof File)) {
			throw new Exception\ImageCannotBeInitializedException($path);
		}

		return $path;
	}

	/** @return mixed $type The binary image data, ready to be written to a file. */
	public function getBinary() {
		ob_start();
		switch ($this->getType()) {
			case static::TYPE_PNG:
				imagePNG($this->data);
				break;
			case static::TYPE_JPEG:
				imageJPEG($this->data);
				break;
		}
		return ob_get_clean();
	}

	/** @return resource The image data. */
	public function getData() {
		return $this->data;
	}

	/** @return string The type of the image, either static::TYPE_JPEG or static::TYPE_PNG. */
	public function getType() {
		return $this->type;
	}

	/** @return string The valid mime type for the loaded image. */
	public function getMimeType() {
		return 'image/' . $this->getType();
	}

}
