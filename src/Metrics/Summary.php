<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Metrics;

use Iterator;
use OpenMetrics\Exposition\Text\Collections\GaugeCollection;
use OpenMetrics\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetrics\Exposition\Text\Interfaces\NamesMetric;
use OpenMetrics\Exposition\Text\Interfaces\ProvidesMetricLines;
use OpenMetrics\Exposition\Text\Metrics\Aggregations\Count;
use OpenMetrics\Exposition\Text\Metrics\Aggregations\Sum;
use OpenMetrics\Exposition\Text\Metrics\Summary\Quantile;
use const SORT_ASC;
use const SORT_NUMERIC;

final class Summary implements ProvidesMetricLines
{
	/** @var NamesMetric */
	private $metricName;

	/** @var string */
	private $help;

	/** @var string */
	private $metricType;

	/** @var array */
	private $quantiles;

	private function __construct( NamesMetric $metricName )
	{
		$this->metricName = $metricName;
		$this->help       = '';
		$this->metricType = 'summary';
		$this->quantiles  = [];
	}

	/**
	 * @param GaugeCollection $collection
	 * @param array           $quantiles
	 * @param string          $suffix
	 *
	 * @throws InvalidArgumentException
	 * @return Summary
	 */
	public static function fromGaugeCollectionWithQuantiles(
		GaugeCollection $collection,
		array $quantiles,
		string $suffix = ''
	) : self
	{
		$summary = new self( $collection->getMetricName()->withSuffix( $suffix ) );

		foreach ( $summary->getQuantiles( $collection, $quantiles ) as $quantile )
		{
			$summary->quantiles[] = $quantile;
		}

		$summary->quantiles[] = Sum::new( $collection->sumMeasuredValues() );
		$summary->quantiles[] = Count::new( $collection->count() );

		return $summary;
	}

	/**
	 * @param GaugeCollection $collection
	 * @param array           $quantiles
	 *
	 * @throws InvalidArgumentException
	 * @return iterable
	 */
	private function getQuantiles( GaugeCollection $collection, array $quantiles ) : iterable
	{
		sort( $quantiles, SORT_NUMERIC | SORT_ASC );

		foreach ( $quantiles as $quantile )
		{
			yield Quantile::new( $quantile, $collection->getQuantile( $quantile ) );
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

		foreach ( $this->quantiles as $quantile )
		{
			yield $this->metricName->toString() . $quantile->getSampleString();
		}
	}

	public function getMetricsString() : string
	{
		return implode( "\n", iterator_to_array( $this->getMetricLines(), false ) );
	}
}