<?php defined('TESTING') or exit(1);

use Intellex\ImageStamp\Image;

Tester::test([

	'Watermark' => [ function ($output, $image, $stamp, $transformation) {

		// Load image, load stamp and write to output file
		$image = Image::fromFile($image);
		$stamp = new \Intellex\ImageStamp\Stamp($stamp, $transformation);
		$image->watermark($stamp);
		$output = $image->writeToFile([ __DIR__, 'tmp', "{$output}.{$image->getType()}" ]);

		// Make sure the output is readable and clean up.
		$result = $output->isReadable();
		$output->delete();
		return $result;

	}, [
		'Watermark' => [
			[
				'Watermark',
				[ __DIR__, 'files', 'image.jpeg' ],
				[ __DIR__, 'files', 'watermark.png' ],
				new \Intellex\ImageStamp\Transformation(
					120, 120,
					0, 1)
			],
			true ] ] ]
]);
