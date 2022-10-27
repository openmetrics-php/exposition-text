<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Collections;

use Iterator;
use OpenMetrics\Exposition\Text\Interfaces\NamesMetric;
use OpenMetrics\Exposition\Text\Metrics\Gauge;
use function count;
use function implode;
use function iterator_to_array;
use const PHP_ROUND_HALF_UP;

final class GaugeCollection extends AbstractMetricCollection
{
	/** @var array|Gauge[] */
	private $gauges = [];

	/** @var bool */
	private $sorted = false;

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
		$this->gauges[] = $gauge;

		if ( [] !== $gauges )
		{
			$this->gauges = array_merge( $this->gauges, $gauges );
		}

		$this->sorted = false;
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
		$this->sortMeasuredValues();

		$count = 0;
		foreach ( $this->gauges as $gauge )
		{
			if ( $gauge->getMeasuredValue() > $bound )
			{
				break;
			}

			$count++;
		}

		return $count;
	}

	public function getQuantile( float $quantile ) : float
	{
		$this->sortMeasuredValues();

		$index = (int)round( $this->count() * $quantile, 0, PHP_ROUND_HALF_UP ) - 1;

		return $this->gauges[ $index ]->getMeasuredValue();
	}

	private function sortMeasuredValues() : void
	{
		if ( $this->sorted )
		{
			return;
		}

		usort(
			$this->gauges,
			function ( Gauge $a, Gauge $b )
			{
				return $a->getMeasuredValue() <=> $b->getMeasuredValue();
			}
		);

		$this->sorted = true;
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
}