<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text;

use Iterator;
use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\LogicException;
use OpenMetricsPhp\Exposition\Text\Interfaces\CollectsMetrics;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use function call_user_func;
use function get_class;
use function iterator_to_array;

final class Metrics implements CollectsMetrics
{
	/** @var array|CollectsMetrics[] */
	private $collections;

	private function __construct()
	{
		$this->collections = [];
	}

	public static function new() : self
	{
		return new self();
	}

	/**
	 * @param NamesMetric $metricName
	 *
	 * @throws LogicException
	 * @return GaugeCollection
	 */
	public function collectGauges( NamesMetric $metricName ) : GaugeCollection
	{
		/** @var GaugeCollection $collection */
		$collection = $this->getCollectionForMetricName( $metricName, GaugeCollection::class );

		return $collection;
	}

	/**
	 * @param NamesMetric $metricName
	 * @param string      $collectionClass
	 *
	 * @throws LogicException
	 * @return CollectsMetrics
	 */
	private function getCollectionForMetricName( NamesMetric $metricName, string $collectionClass ) : CollectsMetrics
	{
		$key = $metricName->toString();
		if ( !isset( $this->collections[ $key ] ) )
		{
			$this->collections[ $key ] = call_user_func( [$collectionClass, 'new'], $metricName );
		}

		$this->guardCollectionIsInstanceOfClass( $this->collections[ $key ], $collectionClass );

		return $this->collections[ $key ];
	}

	/**
	 * @param        $collection
	 * @param string $className
	 *
	 * @throws LogicException
	 */
	private function guardCollectionIsInstanceOfClass( $collection, string $className ) : void
	{
		if ( !($collection instanceof $className) )
		{
			throw new LogicException(
				sprintf(
					'Requested collection type does not match existing collection: Requested %s vs. existing %s',
					$className,
					get_class( $collection )
				)
			);
		}
	}

	/**
	 * @param NamesMetric $metricName
	 *
	 * @throws LogicException
	 * @return CounterCollection
	 */
	public function collectCounters( NamesMetric $metricName ) : CounterCollection
	{
		/** @var CounterCollection $collection */
		$collection = $this->getCollectionForMetricName( $metricName, CounterCollection::class );

		return $collection;
	}

	public function count() : int
	{
		$count = 0;

		foreach ( $this->collections as $collection )
		{
			$count += $collection->count();
		}

		return $count;
	}

	public function getMetricLines() : Iterator
	{
		foreach ( $this->collections as $collection )
		{
			yield from $collection->getMetricLines();
		}
	}

	public function getMetricsString() : string
	{
		return implode( "\n", iterator_to_array( $this->getMetricLines(), false ) );
	}
}