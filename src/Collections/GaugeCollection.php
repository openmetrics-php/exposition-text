<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Collections;

use Countable;
use OpenMetricsPhp\Exposition\Text\Exceptions\MetricNameMismatchException;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use function array_merge;
use function count;
use function implode;

final class GaugeCollection implements Countable
{
	/** @var NamesMetric */
	private $metricName;

	/** @var array|Gauge[] */
	private $gauges = [];

	private function __construct( NamesMetric $metricName )
	{
		$this->metricName = $metricName;
	}

	public static function new( NamesMetric $metricName ) : self
	{
		return new self( $metricName );
	}

	/**
	 * @param NamesMetric $metricName
	 * @param Gauge       $gauge
	 * @param Gauge       ...$gauges
	 *
	 * @throws MetricNameMismatchException
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
	 *
	 * @throws MetricNameMismatchException
	 */
	public function add( Gauge $gauge, Gauge ...$gauges ) : void
	{
		$this->guardGaugesMatchMetricName( $gauge, ...$gauges );

		$this->gauges = array_merge( $this->gauges, [$gauge], $gauges );
	}

	/**
	 * @param Gauge $gauge
	 * @param Gauge ...$gauges
	 *
	 * @throws MetricNameMismatchException
	 */
	private function guardGaugesMatchMetricName( Gauge $gauge, Gauge ...$gauges ) : void
	{
		$this->guardGaugeMatchesMetricName( $gauge );
		foreach ( $gauges as $loopGauge )
		{
			$this->guardGaugeMatchesMetricName( $loopGauge );
		}
	}

	/**
	 * @param Gauge $gauge
	 *
	 * @throws MetricNameMismatchException
	 */
	private function guardGaugeMatchesMetricName( Gauge $gauge ) : void
	{
		if ( !$this->metricName->equals( $gauge->getMetricName() ) )
		{
			throw MetricNameMismatchException::forCollectionItem( $this->metricName, $gauge->getMetricName() );
		}
	}

	public function count() : int
	{
		return count( $this->gauges );
	}

	public function getMetricStrings() : string
	{
		if ( 0 === count( $this->gauges ) )
		{
			return '';
		}

		$strings = [];

		foreach ( $this->gauges as $index => $gauge )
		{
			if ( 0 === $index )
			{
				$strings[] = $gauge->getTypeString();
			}

			$strings[] = $gauge->getHelpString();
			$strings[] = $gauge->getSampleString();
		}

		return implode( "\n", array_filter( $strings ) );
	}
}