<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics\Summary;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Types\Label;

final class Quantile
{
	/** @var Label */
	private $quantile;

	/** @var float */
	private $value;

	/**
	 * @param float $quantile
	 * @param float $value
	 *
	 * @throws InvalidArgumentException
	 */
	private function __construct( float $quantile, float $value )
	{
		$this->guardQuantileIsValid( $quantile );

		$this->quantile = Label::fromNameAndValue( 'quantile', (string)$quantile );
		$this->value    = $value;
	}

	/**
	 * @param float $quantile
	 *
	 * @throws InvalidArgumentException
	 */
	private function guardQuantileIsValid( float $quantile ) : void
	{
		if ( 0 > $quantile || 1 < $quantile )
		{
			throw new InvalidArgumentException( 'Invalid value for quantile; must be 0 <= φ <= 1' );
		}
	}

	/**
	 * @param float $quantile
	 * @param float $value
	 *
	 * @throws InvalidArgumentException
	 * @return Quantile
	 */
	public static function new( float $quantile, float $value ) : self
	{
		return new self( $quantile, $value );
	}

	public function getSampleString() : string
	{
		return sprintf( '{%s} %f', $this->quantile->getLabelString(), $this->value );
	}
}