<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Types;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;

final class MetricName implements NamesMetric
{
	/** @var string */
	private $metricName;

	/**
	 * @param string $metricName
	 *
	 * @throws InvalidArgumentException
	 */
	private function __construct( string $metricName )
	{
		$trimmedMetricName = trim( $metricName );

		$this->guardMetricNameIsValid( $trimmedMetricName );

		$this->metricName = $trimmedMetricName;
	}

	/**
	 * @param string $metricName
	 *
	 * @throws InvalidArgumentException
	 * @return MetricName
	 */
	public static function fromString( string $metricName ) : self
	{
		return new self( $metricName );
	}

	/**
	 * @param string $metricName
	 *
	 * @throws InvalidArgumentException
	 */
	private function guardMetricNameIsValid( string $metricName ) : void
	{
		if ( !preg_match( '#^[a-z_:][a-z\d_:]*$#i', $metricName ) )
		{
			throw new InvalidArgumentException( 'Invalid metric name.' );
		}
	}

	public function toString() : string
	{
		return $this->metricName;
	}

	public function equals( NamesMetric $other ) : bool
	{
		return $this->toString() === $other->toString();
	}

	/**
	 * @param string $suffix
	 *
	 * @throws InvalidArgumentException
	 * @return NamesMetric
	 */
	public function withSuffix( string $suffix ) : NamesMetric
	{
		return new self( $this->metricName . $suffix );
	}
}