<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics\Histogram;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesSampleString;
use OpenMetricsPhp\Exposition\Text\Types\Label;

final class Bucket implements ProvidesSampleString
{
	/** @var Label */
	private $le;

	/** @var float */
	private $count;

	/**
	 * @param float $le
	 * @param int   $count
	 *
	 * @throws InvalidArgumentException
	 */
	private function __construct( float $le, int $count )
	{
		$this->le    = Label::fromNameAndValue(
			'le',
			$this->getNumberAsStringWithLowestDecimalCount( $le )
		);
		$this->count = $count;
	}

	private function getNumberAsStringWithLowestDecimalCount( float $number ) : string
	{
		return sprintf( sprintf( '%%.%df', max( 1, $this->getDecimalCount( $number ) ) ), $number );
	}

	private function getDecimalCount( float $f ) : int
	{
		$num = 0;
		while ( (string)$f !== (string)round( $f ) && !is_infinite( $f ) )
		{
			$f *= 10;
			$num++;
		}

		return $num;
	}

	/**
	 * @param float $le
	 * @param int   $value
	 *
	 * @throws InvalidArgumentException
	 * @return Bucket
	 */
	public static function new( float $le, int $value ) : self
	{
		return new self( $le, $value );
	}

	public function getSampleString() : string
	{
		return sprintf( '_bucket{%s} %d', $this->le->getLabelString(), $this->count );
	}
}