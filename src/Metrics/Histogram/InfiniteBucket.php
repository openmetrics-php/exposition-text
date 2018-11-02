<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics\Histogram;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesSampleString;
use OpenMetricsPhp\Exposition\Text\Types\Label;

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