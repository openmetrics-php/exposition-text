<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Metrics\Histogram;

use OpenMetrics\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetrics\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetrics\Exposition\Text\Interfaces\ProvidesSampleString;
use OpenMetrics\Exposition\Text\Types\Label;

final class InfiniteBucket implements ProvidesSampleString
{
	/** @var ProvidesNamedValue */
	private $le;

	/** @var int */
	private $count;

	/**
	 * @param int $count
	 *
	 * @throws InvalidArgumentException
	 */
	private function __construct( int $count )
	{
		$this->le    = Label::fromNameAndValue( 'le', '+Inf' );
		$this->count = $count;
	}

	/**
	 * @param int $count
	 *
	 * @throws InvalidArgumentException
	 * @return InfiniteBucket
	 */
	public static function new( int $count ) : self
	{
		return new self( $count );
	}

	public function getSampleString() : string
	{
		return sprintf( '_bucket{%s} %d', $this->le->getLabelString(), $this->count );
	}
}