<?php require '../../vendor/autoload.php';

use Intellex\Filesystem\File;
use Intellex\ImageStamp\Image;

/**
 * Class WatermarkImage ads a watermark to the image.
 */
class WatermarkImage extends \Intellex\ImageStamp\Proxy {

	/** @inheritdoc */
	protected function defineCache() {
		return new File([ dirname(__DIR__), 'tmp', $this->getRequestPath() ]);
	}

	/** @inheritdoc */
	protected function defineCacheTimeToLive() {
		return 10;
	}

	/** @inheritdoc */
	protected function handle() {

		// Skip favicon.ico request
		if($this->getRequestPath() === '/favicon.ico') {
			return null;
		}

		// Load image, load stamp and write to output file
		$image = Image::fromFile([ dirname(__DIR__), 'files', 'image.jpeg' ]);
		$stamp = new \Intellex\ImageStamp\Stamp([ dirname(__DIR__), 'files', 'watermark.png' ]);
		$image->watermark($stamp);

		// Send the result
		header('Content-Type: ' . $image->getMimeType());
		return $image->getBinary();
	}

	/** @inheritdoc */
	protected function isDebug() {
		return false;
	}

}

new WatermarkImage();
