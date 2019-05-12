<?php defined('TESTING') or exit(1);

use Intellex\ImageStamp\Image;

Tester::test([

	'Actions' => [ function ($output, $image, $stamp, $top, $left, $transformation) {

		// Load image, load stamp and write to output file
		$image = Image::fromFile($image);
		$stamp = new \Intellex\ImageStamp\Stamp($stamp, $transformation);
		$image->stamp($stamp, $top, $left);
		$output = $image->writeToFile([ __DIR__, 'tmp', "{$output}.{$image->getType()}" ]);

		// Make sure the output is readable and clean up.
		$result = $output->isReadable();
		$output->delete();
		return $result;

	}, [
		'Simple'  => [
			[
				'Simple',
				[ __DIR__, 'files', 'image.jpeg' ],
				[ __DIR__, 'files', 'stamp.png' ],
				280, 120,
				new \Intellex\ImageStamp\Transformation(
					400, 400,
					0, 1)
			],
			true ],
		'Rotate'  => [
			[
				'Rotate',
				[ __DIR__, 'files', 'image.jpeg' ],
				[ __DIR__, 'files', 'stamp.png' ],
				280, 120,
				new \Intellex\ImageStamp\Transformation(
					400, 400,
					90, 1)
			],
			true ],
		'Opacity' => [
			[
				'Opacity',
				[ __DIR__, 'files', 'image.jpeg' ],
				[ __DIR__, 'files', 'stamp.png' ],
				280, 120,
				new \Intellex\ImageStamp\Transformation(
					400, 400,
					0, 0.2)
			],
			true ],
		'Combine' => [
			[
				'Combine',
				[ __DIR__, 'files', 'image.jpeg' ],
				[ __DIR__, 'files', 'stamp.png' ],
				730, 500,
				new \Intellex\ImageStamp\Transformation(
					100, 100,
					25, 0.5)
			],
			true ] ] ]
]);
