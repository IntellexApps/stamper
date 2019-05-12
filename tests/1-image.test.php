<?php defined('TESTING') or exit(1);

use Intellex\Filesystem\File;
use Intellex\ImageStamp\Exception\ImageCannotBeInitializedException;
use Intellex\ImageStamp\Image;

// Run tests
Tester::test([

	'Image::assertFile()' => [ function ($source) {
		return Tester::invokePrivateStaticMethod('\\Intellex\\ImageStamp\\Image', 'assertFile', [ $source ]);
	}, [
		'Parse from string'   => [
			[
				__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'stamp.png'
			],
			new File(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'stamp.png')
		],
		'Parse from an array' => [
			[
				[ __DIR__, 'files', 'stamp.png' ]
			],
			new File(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'stamp.png')
		],
		'Parse from a File'   => [
			[
				new File(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'image.jpeg')
			],
			new File(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'image.jpeg')
		],
		'Exception'           => [
			[
				123
			],
			new ImageCannotBeInitializedException(123)
		] ] ],

	'Image::fromFile()' => [ function ($source) {
		return Image::fromFile($source)->getType();
	}, [
		'Load image from a string'          => [
			[
				__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'stamp.png'
			],
			Image::TYPE_PNG
		],
		'Load image from an array'          => [
			[
				[ __DIR__, 'files', 'stamp.png' ]
			],
			Image::TYPE_PNG
		],
		'Load image from a File'            => [
			[
				new File(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'image.jpeg')
			],
			Image::TYPE_JPEG
		],
		'Load image from a wacky extension' => [
			[
				new File(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'image.JpG')
			],
			Image::TYPE_JPEG
		],
		'Error loading'                     => [
			[
				null
			],
			new \Intellex\ImageStamp\Exception\ImageCannotBeInitializedException(null)
		],
		'Unsupported format'                => [
			[
				[ __DIR__, 'files', 'no-image.txt' ]
			],
			new \Intellex\ImageStamp\Exception\UnsupportedImageFormatException('text/plain', __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'no-image.txt')
		] ] ],

	'Image::writeToFile()' => [ function ($file) {
		$image = Image::fromFile([ __DIR__, 'files', 'image.jpeg' ]);
		$output = $image->writeToFile($file);
		$readable = $output->isReadable();
		$output->delete();
		return $readable;
	}, [
		'Write' => [
			[
				[ __DIR__, 'tmp', 'writeToFileTest.jpeg' ]
			],
			true,
		] ] ]
]);
