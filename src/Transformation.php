<?php namespace Intellex\ImageStamp;

/**
 * Class Action describes on how to implement the stamp onto an image.
 *
 * @package Intellex\ImageStamp
 */
class Transformation {

	/** @var int|null The width of the stamp. */
	private $width;

	/** @var int|null The height of the stamp. */
	private $height;

	/** @var int|null The amount in which to rotate the image anti-clockwise, in degrees. */
	private $rotation;

	/** @var float|null The opacity of the stamp, as a value between 0.0 and 1.0. */
	private $opacity;

	/**
	 * Initialize the Action.
	 *
	 * @param int|null   $width    The width of the stamp.
	 * @param int|null   $height   The The height of the stamp.
	 * @param int|null   $rotation The amount in which to rotate the image anti-clockwise, in
	 *                             degrees.
	 * @param float|null $opacity  The opacity of the stamp, as a value between 0.0 and 1.0.
	 */
	public function __construct($width = null, $height = null, $rotation = null, $opacity = null) {
		$this->width = $width;
		$this->height = $height;
		$this->rotation = $rotation !== null ? $rotation % 360 : null;
		$this->opacity = $rotation !== null ? max(0, min(1, $opacity)) : null;
	}

	/** @param int|null $width The width of the stamp. */
	public function setWidth($width) {
		$this->width = $width;
	}

	/** @param int|null $height The height of the stamp. */
	public function setHeight($height) {
		$this->height = $height;
	}

	/** @param int|null $rotation The amount in which to rotate the image anti-clockwise, in degrees. */
	public function setRotation($rotation) {
		$this->rotation = $rotation;
	}

	/** @param float|null $opacity The opacity of the stamp, as a value between 0.0 and 1.0. */
	public function setOpacity($opacity) {
		$this->opacity = $opacity;
	}

	/** @return int|null The width of the stamp. */
	public function getWidth() {
		return $this->width;
	}

	/** @return int|null The height of the stamp. */
	public function getHeight() {
		return $this->height;
	}

	/** @return int|null The amount in which to rotate the image anti-clockwise, in degrees. */
	public function getRotation() {
		return $this->rotation;
	}

	/** @return float|null The opacity of the stamp, as a value between 0.0 and 1.0. */
	public function getOpacity() {
		return $this->opacity;
	}

}
