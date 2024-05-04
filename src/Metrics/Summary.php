<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics;

use Iterator;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesMetricLines;
use OpenMetricsPhp\Exposition\Text\Metrics\Aggregations\Count;
use OpenMetricsPhp\Exposition\Text\Metrics\Aggregations\Sum;
use OpenMetricsPhp\Exposition\Text\Metrics\Summary\Quantile;
use Traversable;
use function iterator_to_array;
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

	/** @var array<Quantile|Sum|Count> */
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
	 * @param array<float> $quantiles
	 * @param string          $suffix
	 *
	 * @return Summary
	 * @throws InvalidArgumentException
	 */
	public static function fromGaugeCollectionWithQuantiles(
		GaugeCollection $collection,
		array $quantiles,
		string $suffix = ''
	) : self
	{
		$summary            = new self( $collection->getMetricName()->withSuffix( $suffix ) );
		$summary->quantiles = iterator_to_array( $summary->getQuantiles( $collection, $quantiles ), true );

		$summary->quantiles[] = Sum::new( $collection->sumMeasuredValues() );
		$summary->quantiles[] = Count::new( $collection->count() );

		return $summary;
	}

	/**
	 * @param GaugeCollection $collection
	 * @param array<float> $quantiles
	 *
	 * @return Iterator<Quantile>
	 * @throws InvalidArgumentException
	 */
	private function getQuantiles( GaugeCollection $collection, array $quantiles ) : Iterator
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

	public function getMetricLines() : Traversable
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