<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Interfaces\CollectsLabels;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesMeasuredValue;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesSampleString;
use function sprintf;

final class Gauge implements ProvidesSampleString, ProvidesMeasuredValue
{
	/** @var float */
	private $gaugeValue;

	/** @var int|null */
	private $timestamp;

	/** @var CollectsLabels */
	private $labels;

	/**
	 * @param float    $gaugeValue
	 * @param int|null $timestamp
	 */
	private function __construct( float $gaugeValue, ?int $timestamp = null )
	{
		$this->gaugeValue = $gaugeValue;
		$this->timestamp  = $timestamp;
		$this->labels     = LabelCollection::new();
	}

	public static function fromValue( float $gaugeValue ) : self
	{
		return new self( $gaugeValue );
	}

	public static function fromValueAndTimestamp(
		float $gaugeValue,
		int $timestamp
	) : self
	{
		return new self( $gaugeValue, $timestamp );
	}

	public function withLabels( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : self
	{
		$this->addLabels( $label, ...$labels );

		return $this;
	}

	public function withLabelCollection( CollectsLabels $labels ) : self
	{
		foreach ( $labels->getIterator() as $label )
		{
			$this->addLabels( $label );
		}

		return $this;
	}

	public function addLabels( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : void
	{
		$this->labels->add( $label, ...$labels );
	}

	public function getSampleString() : string
	{
		return sprintf(
			'%s %f%s',
			$this->labels->getCombinedLabelString(),
			$this->gaugeValue,
			null !== $this->timestamp ? (' ' . $this->timestamp) : ''
		);
	}

	public function getMeasuredValue() : float
	{
		return $this->gaugeValue;
	}
}