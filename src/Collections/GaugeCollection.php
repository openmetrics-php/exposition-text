<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Collections;

use Countable;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use function array_merge;
use function count;
use function implode;

final class GaugeCollection implements Countable
{
	private const TYPE = 'gauge';

	/** @var NamesMetric */
	private $metricName;

	/** @var string */
	private $help;

	/** @var array|Gauge[] */
	private $gauges = [];

	private function __construct( NamesMetric $metricName )
	{
		$this->metricName = $metricName;
		$this->help       = '';
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

	public function withHelp( string $helpText ) : self
	{
		$this->setHelp( $helpText );

		return $this;
	}

	public function setHelp( string $helpText ) : void
	{
		$this->help = str_replace( "\n", ' ', trim( $helpText ) );
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

		$strings = [
			$this->getTypeString(),
			$this->getHelpString(),
		];

		foreach ( $this->gauges as $index => $gauge )
		{
			$strings[] = $this->metricName->toString() . $gauge->getSampleString();
		}

		return implode( "\n", array_filter( $strings ) );
	}

	private function getTypeString() : string
	{
		return sprintf( '# TYPE %s %s', $this->metricName->toString(), self::TYPE );
	}

	private function getHelpString() : string
	{
		if ( '' === $this->help )
		{
			return '';
		}

		return sprintf( '# HELP %s %s', $this->metricName->toString(), $this->help );
	}
}