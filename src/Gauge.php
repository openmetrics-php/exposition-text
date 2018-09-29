<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text;

use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use function sprintf;
use function str_replace;

final class Gauge
{
	private const TYPE = 'gauge';

	/** @var MetricName */
	private $metricName;

	/** @var float */
	private $gaugeValue;

	/** @var int */
	private $timestamp;

	/** @var LabelCollection */
	private $labels;

	/** @var string */
	private $help;

	/**
	 * @param MetricName $metricName
	 * @param float      $gaugeValue
	 * @param int|null   $timestamp
	 */
	private function __construct( MetricName $metricName, float $gaugeValue, ?int $timestamp = null )
	{
		$this->metricName = $metricName;
		$this->gaugeValue = $gaugeValue;
		$this->timestamp  = $timestamp;
		$this->labels     = LabelCollection::new();
		$this->help       = '';
	}

	/**
	 * @param string $metricName
	 * @param float  $gaugeValue
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @return Gauge
	 */
	public static function fromMetricNameAndValue( string $metricName, float $gaugeValue ) : self
	{
		return new self( MetricName::fromString( $metricName ), $gaugeValue );
	}

	/**
	 * @param string $metricName
	 * @param float  $gaugeValue
	 * @param int    $timestamp
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @return Gauge
	 */
	public static function fromMetricNameValueAndTimestamp(
		string $metricName,
		float $gaugeValue,
		int $timestamp
	) : self
	{
		return new self( MetricName::fromString( $metricName ), $gaugeValue, $timestamp );
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

	public function getMetricName() : MetricName
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