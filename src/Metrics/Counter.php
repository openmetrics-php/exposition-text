<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;

final class Counter
{
	/** @var int */
	private $counterValue;

	/** @var int|null */
	private $timestamp;

	/** @var LabelCollection */
	private $labels;

	/**
	 * @param int      $counterValue
	 * @param int|null $timestamp
	 */
	private function __construct( int $counterValue, ?int $timestamp = null )
	{
		$this->counterValue = $counterValue;
		$this->timestamp    = $timestamp;
		$this->labels       = LabelCollection::new();
	}

	public static function fromValue( int $counterValue ) : self
	{
		return new self( $counterValue );
	}

	public static function fromValueAndTimestamp( int $counterValue, int $timestamp ) : self
	{
		return new self( $counterValue, $timestamp );
	}

	public function withLabels( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : self
	{
		$this->addLabels( $label, ...$labels );

		return $this;
	}

	public function withLabelCollection( LabelCollection $labels ) : self
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
			'%s %d%s',
			$this->labels->getCombinedLabelString(),
			$this->counterValue,
			null !== $this->timestamp ? (' ' . $this->timestamp) : ''
		);
	}
}