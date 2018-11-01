<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics;

use Iterator;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesSampleString;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram\HistogramBucket;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram\HistogramCount;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram\HistogramInfBucket;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram\HistogramSum;
use function iterator_to_array;
use function sort;
use const SORT_ASC;
use const SORT_NUMERIC;

final class Histogram
{
	/** @var NamesMetric */
	private $metricName;

	/** @var string */
	private $help;

	/** @var string */
	private $metricType;

	/** @var array|ProvidesSampleString[] */
	private $buckets;

	private function __construct( NamesMetric $metricName )
	{
		$this->metricName = $metricName;
		$this->help       = '';
		$this->metricType = 'histogram';
		$this->buckets    = [];
	}

	/**
	 * @param GaugeCollection $collection
	 * @param array           $bounds
	 * @param string          $suffix
	 *
	 * @throws InvalidArgumentException
	 * @return Histogram
	 */
	public static function fromGaugeCollectionWithBounds(
		GaugeCollection $collection,
		array $bounds,
		string $suffix = ''
	) : self
	{
		$histogram = new self( $collection->getMetricName()->withSuffix( $suffix ) );

		foreach ( $histogram->getBucketsForBounds( $collection, $bounds ) as $bucket )
		{
			$histogram->buckets[] = $bucket;
		}

		$countMeasurements    = $collection->count();
		$histogram->buckets[] = HistogramInfBucket::new( $countMeasurements );
		$histogram->buckets[] = HistogramSum::new( $collection->sumMeasuredValues() );
		$histogram->buckets[] = HistogramCount::new( $countMeasurements );

		return $histogram;
	}

	/**
	 * @param GaugeCollection $collection
	 * @param array           $bounds
	 *
	 * @throws InvalidArgumentException
	 * @return iterable
	 */
	private function getBucketsForBounds( GaugeCollection $collection, array $bounds ) : iterable
	{
		sort( $bounds, SORT_NUMERIC | SORT_ASC );

		foreach ( $bounds as $bound )
		{
			yield HistogramBucket::new( $bound, $collection->countMeasuredValuesLowerThanOrEqualTo( $bound ) );
		}
	}

	public function withHelp( string $helpText ) : self
	{
		$this->setHelp( $helpText );

		return $this;
	}

	public function setHelp( string $helpText ) : void
	{
		$this->help = str_replace( "\n", ' ', trim( $helpText ) );
	}

	private function getTypeString() : string
	{
		return sprintf( '# TYPE %s %s', $this->metricName->toString(), $this->metricType );
	}

	private function getHelpString() : string
	{
		if ( '' === $this->help )
		{
			return '';
		}

		return sprintf( '# HELP %s %s', $this->metricName->toString(), $this->help );
	}

	public function getMetricLines() : Iterator
	{
		yield $this->getTypeString();

		$helpString = $this->getHelpString();
		if ( '' !== $helpString )
		{
			yield $helpString;
		}

		foreach ( $this->buckets as $bucket )
		{
			yield $this->metricName->toString() . $bucket->getSampleString();
		}
	}

	public function getMetricsString() : string
	{
		return implode( "\n", iterator_to_array( $this->getMetricLines(), false ) );
	}
}