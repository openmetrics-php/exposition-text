<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Collections;

use Generator;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use function array_merge;
use function count;
use function implode;
use function iterator_to_array;

final class GaugeCollection extends AbstractMetricCollection
{
	/** @var array|Gauge[] */
	private $gauges = [];

	public static function new( NamesMetric $metricName ) : self
	{
		return new static( $metricName, 'gauge' );
	}

	/**
	 * @param NamesMetric $metricName
	 * @param Gauge       $gauge
	 * @param Gauge       ...$gauges
	 *
	 * @return GaugeCollection
	 */
	public static function fromGauges( NamesMetric $metricName, Gauge $gauge, Gauge ...$gauges ) : self
	{
		$collection = self::new( $metricName );
		$collection->add( $gauge, ...$gauges );

		return $collection;
	}

	/**
	 * @param Gauge $gauge
	 * @param Gauge ...$gauges
	 */
	public function add( Gauge $gauge, Gauge ...$gauges ) : void
	{
		$this->gauges = array_merge( $this->gauges, [$gauge], $gauges );
	}

	public function count() : int
	{
		return count( $this->gauges );
	}

	public function getMetricLines() : Generator
	{
		if ( 0 === count( $this->gauges ) )
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

	public function getMetricStrings() : string
	{
		return implode( "\n", iterator_to_array( $this->getMetricLines(), false ) );
	}
}