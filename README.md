# Lightweight PHP library for adding stamps and watermarks to images on the fly

Very useful when you want to keep you original images inaccessible and serve their watermarked version only.

* Supports both __single stamps__ and __tiled watermarks__.
* Integrated support for __cache__, with seamless implementation.
* Easy transformations such as: __resize__, __rotate__ and __opacity__.

Setup
--------------------
In order to fully utilize this library, a web server setup is necessary. Web server should recognize all requests going out for images and than redirect them to your proxy script.

Here is an example of nginx configuration:
```nginx
server {
	
	...

	# This should be the first location section in your config 
	location /images/ {
		fastcgi_pass	unix:/run/php/php7.1-fpm.sock;
		try_files	/proxy/watermark.php =404;
		include		fastcgi.conf;
	}
	
	...
}
``` 

Examples
--------------------

Check out <code>/tests/proxy</code> directory for fully working snippets.

##### Get the stamp or watermark

```php
<?php

// Load stamp or watermark from from any PNG file
$stamp = new Stamp('/path/to/your/stamp.png');

// Feel free to play around with transformations
$stamp->getSource()->resize(200, 100); // Resize to 200 x 100
$stamp->getSource()->rotate(180);      // Rotate by 180 degrees
$stamp->getSource()->setOpacity(0.5);  // Make it 50% translucent

// You can achieve the same effect using Transformation class
$transformation = new Transformation(
	200, 100,   // Width and height
	180,        // Rotation
	0.5	        // Opacity
);
$stamp->getSource()->apply($transformation);

// All of the transformation can be done on the Image class in the same way!
```

##### Apply it to an image

```PHP
<?php

// Load the target image from either JPEG or PNG images
$image = Image::fromFile('/path/to/your/target-image.jpeg');

// Execute
$image->stamp($stamp, 100, 100); // Single stamp starting on position 100 x 100
$image->watermark($stamp);       // Tiled watermark accros the whole image

```



#### Create a simple file that will be used as a proxy

Load the target image from either JPEG or PNG images.
```php
<?php require '../vendor/autoload.php';

/**
 * Class StampImage ads a stamp to the image.
 */
class StampImage extends \Intellex\ImageStamp\Proxy {

	/** @inheritdoc */
	protected function defineCache() {
		return new \Intellex\Filesystem\File('/path/to/where/this/response/will/be/cached.jpeg');
	}

	/** @inheritdoc */
	protected function defineCacheTimeToLive() {
		return 3600; // Cache for 1 hour
	}

	/** @inheritdoc */
	protected function handle() {
		// TODO the magic here
		return 'THE DATA'; // This value will be automatically cached in the file define in defineCache() method
	}

}

new StampImage();
```

##### Full example
```PHP
<?php require '../vendor/autoload.php';

class StampImage extends Proxy {

	/** @inheritdoc */
	protected function defineCache() {
		return new File('/path/to/where/this/response/will/be/cached.jpeg');
	}

	/** @inheritdoc */
	protected function defineCacheTimeToLive() {
		return 3600; // Cache for 1 hour
	}

	/** @inheritdoc */
	protected function handle() {

		// Skip favicon.ico request
		if($this->getRequestPath() === '/favicon.ico') {
			return null;
		}

		// Load image, load stamp and write to output file
		$image = Image::fromFile('/path/to/your/target-image.jpeg');
		$stamp = new Stamp('/path/to/your/target-image.jpeg', new Transformation(400, 400));
		$image->stamp($stamp, 280, 120);

		// Send the result
		header('Content-Type: ' . $image->getMimeType());
		return $image->getBinary();
	}

}

new StampImage();
```

To do
--------------------
1. Make it easier to stamp in the standard positions (ie: center, top, bottom-right, etc...).
2. More tests.

Licence
--------------------
MIT License

Copyright (c) 2019 Intellex

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


Credits
--------------------
Script has been written by the [Intellex](https://intellex.rs/en) team.