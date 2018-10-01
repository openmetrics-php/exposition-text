<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text;

use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;

final class Metrics
{
	/** @var array|GaugeCollection[] */
	private $gaugeCollections;

	private function __construct()
	{
		$this->gaugeCollections = [];
	}

	public static function new() : self
	{
		return new self();
	}

	public function collectGauges( NamesMetric $metricName ) : GaugeCollection
	{
		return $this->getGaugeCollectionForMetricName( $metricName );
	}

	private function getGaugeCollectionForMetricName( NamesMetric $metricName ) : GaugeCollection
	{
		if ( !isset( $this->gaugeCollections[ $metricName->toString() ] ) )
		{
			$this->gaugeCollections[ $metricName->toString() ] = GaugeCollection::new( $metricName );
		}

		return $this->gaugeCollections[ $metricName->toString() ];
	}

	public function getMetricStrings() : string
	{
		$strings = [];

		foreach ( $this->gaugeCollections as $gaugeCollection )
		{
			$strings[] = $gaugeCollection->getMetricStrings();
		}

		return implode( "\n", $strings );
	}
}