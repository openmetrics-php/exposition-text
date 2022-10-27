<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Collections;

use OpenMetrics\Exposition\Text\Interfaces\CollectsMetrics;
use OpenMetrics\Exposition\Text\Interfaces\NamesMetric;

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
	public function withHelp( string $helpText )
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