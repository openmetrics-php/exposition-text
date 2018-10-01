<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use function sprintf;
use function str_replace;

final class Gauge
{
	private const TYPE = 'gauge';

	/** @var NamesMetric */
	private $metricName;

	/** @var float */
	private $gaugeValue;

	/** @var int|null */
	private $timestamp;

	/** @var LabelCollection */
	private $labels;

	/** @var string */
	private $help;

	/**
	 * @param NamesMetric $metricName
	 * @param float       $gaugeValue
	 * @param int|null    $timestamp
	 */
	private function __construct( NamesMetric $metricName, float $gaugeValue, ?int $timestamp = null )
	{
		$this->metricName = $metricName;
		$this->gaugeValue = $gaugeValue;
		$this->timestamp  = $timestamp;
		$this->labels     = LabelCollection::new();
		$this->help       = '';
	}

	/**
	 * @param NamesMetric $metricName
	 * @param float       $gaugeValue
	 *
	 * @return Gauge
	 */
	public static function fromMetricNameAndValue( NamesMetric $metricName, float $gaugeValue ) : self
	{
		return new self( $metricName, $gaugeValue );
	}

	/**
	 * @param NamesMetric $metricName
	 * @param float       $gaugeValue
	 * @param int         $timestamp
	 *
	 * @return Gauge
	 */
	public static function fromMetricNameValueAndTimestamp(
		NamesMetric $metricName,
		float $gaugeValue,
		int $timestamp
	) : self
	{
		return new self( $metricName, $gaugeValue, $timestamp );
	}

	public function addLabels( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : void
	{
		$this->labels->add( $label, ...$labels );
	}

	public function setHelp( string $helpText ) : void
	{
		$this->help = str_replace( "\n", ' ', trim( $helpText ) );
	}

	public function getHelpString() : string
	{
		if ( '' === $this->help )
		{
			return '';
		}

		return sprintf( '# HELP %s %s', $this->metricName->toString(), $this->help );
	}

	public function getMetricName() : NamesMetric
	{
		return $this->metricName;
	}

	public function getTypeString() : string
	{
		return sprintf( '# TYPE %s %s', $this->metricName->toString(), self::TYPE );
	}

	public function getSampleString() : string
	{
		return sprintf(
			'%s%s %f%s',
			$this->metricName->toString(),
			$this->labels->getCombinedLabelString(),
			$this->gaugeValue,
			null !== $this->timestamp ? (' ' . $this->timestamp) : ''
		);
	}

	public function getSingleMetricString() : string
	{
		return sprintf(
			"%s%s\n%s",
			'' !== $this->getHelpString() ? "{$this->getHelpString()}\n" : '',
			$this->getTypeString(),
			$this->getSampleString()
		);
	}
}