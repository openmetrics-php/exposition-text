<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\CollectsLabels;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesSampleString;

final class Counter implements ProvidesSampleString
{
	/** @var float */
	private $counterValue;

	/** @var int|null */
	private $timestamp;

	/** @var CollectsLabels */
	private $labels;

	/**
	 * @param float    $counterValue
	 * @param int|null $timestamp
	 *
	 * @throws InvalidArgumentException
	 */
	private function __construct( float $counterValue, ?int $timestamp = null )
	{
		$this->guardCounterIsValid( $counterValue );

		$this->counterValue = $counterValue;
		$this->timestamp    = $timestamp;
		$this->labels       = LabelCollection::new();
	}

	/**
	 * @param float $counter
	 *
	 * @throws InvalidArgumentException
	 */
	private function guardCounterIsValid( float $counter ) : void
	{
		if ( 0 > $counter )
		{
			throw new InvalidArgumentException( 'Counters must start at 0 and can only go up.' );
		}
	}

	/**
	 * @param float $counterValue
	 *
	 * @return Counter
	 * @throws InvalidArgumentException
	 */
	public static function fromValue( float $counterValue ) : self
	{
		return new self( $counterValue );
	}

	/**
	 * @param float $counterValue
	 * @param int   $timestamp
	 *
	 * @return Counter
	 * @throws InvalidArgumentException
	 */
	public static function fromValueAndTimestamp( float $counterValue, int $timestamp ) : self
	{
		return new self( $counterValue, $timestamp );
	}

	public function withLabels( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : self
	{
		$this->addLabels( $label, ...$labels );

		return $this;
	}

	public function withLabelCollection( CollectsLabels $labels ) : self
	{
		foreach ( $labels as $label )
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
			'_total%s %f%s',
			$this->labels->getCombinedLabelString(),
			$this->counterValue,
			null !== $this->timestamp ? (' ' . $this->timestamp) : ''
		);
	}
}