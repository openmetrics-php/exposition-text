<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics;

use Iterator;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesMetricLines;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesSampleString;
use OpenMetricsPhp\Exposition\Text\Metrics\Aggregations\Count;
use OpenMetricsPhp\Exposition\Text\Metrics\Aggregations\Sum;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram\Bucket;
use OpenMetricsPhp\Exposition\Text\Metrics\Histogram\InfiniteBucket;
use Traversable;
use function iterator_to_array;
use function sort;
use const SORT_ASC;
use const SORT_NUMERIC;

final class Histogram implements ProvidesMetricLines
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
	 * @param array<float> $bounds
	 * @param string          $suffix
	 *
	 * @return Histogram
	 * @throws InvalidArgumentException
	 */
	public static function fromGaugeCollectionWithBounds(
		GaugeCollection $collection,
		array $bounds,
		string $suffix = ''
	) : self
	{
		$histogram = new self( $collection->getMetricName()->withSuffix( $suffix ) );

		$histogram->buckets = iterator_to_array( $histogram->getBucketsForBounds( $collection, $bounds ), true );

		$countMeasurements    = $collection->count();
		$histogram->buckets[] = InfiniteBucket::new( $countMeasurements );
		$histogram->buckets[] = Sum::new( $collection->sumMeasuredValues() );
		$histogram->buckets[] = Count::new( $countMeasurements );

		return $histogram;
	}

	/**
	 * @param GaugeCollection $collection
	 * @param array<float> $bounds
	 *
	 * @return Iterator<ProvidesSampleString>
	 * @throws InvalidArgumentException
	 */
	private function getBucketsForBounds( GaugeCollection $collection, array $bounds ) : Iterator
	{
		sort( $bounds, SORT_NUMERIC | SORT_ASC );

		foreach ( $bounds as $bound )
		{
			yield Bucket::new( $bound, $collection->countMeasuredValuesLowerThanOrEqualTo( $bound ) );
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

	public function getMetricLines() : Traversable
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