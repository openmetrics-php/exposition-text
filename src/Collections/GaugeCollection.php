<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Collections;

use Iterator;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram;
use function array_merge;
use function count;
use function implode;
use function iterator_to_array;

final class GaugeCollection extends AbstractMetricCollection
{
	/** @var array|Gauge[] */
	private $gauges = [];

	public static function withMetricName( NamesMetric $metricName ) : self
	{
		return new static( $metricName, 'gauge' );
	}

	public static function fromGauges( NamesMetric $metricName, Gauge $gauge, Gauge ...$gauges ) : self
	{
		$collection = self::withMetricName( $metricName );
		$collection->add( $gauge, ...$gauges );

		return $collection;
	}

	public function add( Gauge $gauge, Gauge ...$gauges ) : void
	{
		$this->gauges = array_merge( $this->gauges, [$gauge], $gauges );
	}

	public function count() : int
	{
		return count( $this->gauges );
	}

	public function getMetricLines() : Iterator
	{
		if ( 0 === $this->count() )
		{
			return;
		}

		yield $this->getTypeString();

		$helpString = $this->getHelpString();
		if ( '' !== $helpString )
		{
			yield $helpString;
		}

		foreach ( $this->gauges as $gauge )
		{
			yield $this->getMetricName()->toString() . $gauge->getSampleString();
		}
	}

	public function getMetricsString() : string
	{
		return implode( "\n", iterator_to_array( $this->getMetricLines(), false ) );
	}

	public function countMeasuredValuesLowerThanOrEqualTo( float $bound ) : int
	{
		return count(
			array_filter(
				$this->gauges,
				function ( Gauge $counter ) use ( $bound )
				{
					return $counter->getMeasuredValue() <= $bound;
				}
			)
		);
	}

	public function sumMeasuredValues() : float
	{
		$sum = 0;
		foreach ( $this->gauges as $gauge )
		{
			$sum += $gauge->getMeasuredValue();
		}

		return $sum;
	}

	/**
	 * @param array  $bounds
	 * @param string $suffix
	 *
	 * @throws InvalidArgumentException
	 * @return Histogram
	 */
	public function getHistogram( array $bounds, string $suffix = '' ) : Histogram
	{
		return Histogram::fromGaugeCollectionWithBounds( $this, $bounds, $suffix );
	}
}