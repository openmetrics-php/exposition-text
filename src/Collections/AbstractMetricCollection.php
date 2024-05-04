<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Collections;

use OpenMetricsPhp\Exposition\Text\Interfaces\CollectsMetrics;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;

abstract class AbstractMetricCollection implements CollectsMetrics
{
	/** @var NamesMetric */
	private $metricName;

	/** @var string */
	private $metricType;

	/** @var string */
	private $help;

	final protected function __construct( NamesMetric $metricName, string $metricType )
	{
		$this->metricName = $metricName;
		$this->metricType = $metricType;
		$this->help       = '';
	}

	/**
	 * @param string $helpText
	 *
	 * @return static
	 */
	public function withHelp( string $helpText ) : AbstractMetricCollection
    {
		$this->setHelp( $helpText );

		return $this;
	}

	public function setHelp( string $helpText ) : void
	{
		$this->help = str_replace( "\n", ' ', trim( $helpText ) );
	}

	final public function getMetricName() : NamesMetric
	{
		return $this->metricName;
	}

	final protected function getTypeString() : string
	{
		return sprintf( '# TYPE %s %s', $this->metricName->toString(), $this->metricType );
	}

	final protected function getHelpString() : string
	{
		if ( '' === $this->help )
		{
			return '';
		}

		return sprintf( '# HELP %s %s', $this->metricName->toString(), $this->help );
	}
}
