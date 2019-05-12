<?php defined('TESTING') or exit(1);

use Intellex\Filesystem\File;
use Intellex\ImageStamp\Image;

Tester::test([

	'Stamp::__construct()' => [ function ($source) {
		return (new \Intellex\ImageStamp\Stamp($source))->getSource()->getType();
	}, [
		'Load image from string'   => [
			[
				__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'stamp.png'
			],
			Image::TYPE_PNG
		],
		'Load image from an array' => [
			[
				[ __DIR__, 'files', 'stamp.png' ]
			],
			Image::TYPE_PNG
		],
		'Unsupported format'       => [
			[
				new File(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'image.jpeg')
			],
			new \Intellex\ImageStamp\Exception\UnsupportedStampFormatException()
		] ] ]
]);
