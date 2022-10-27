<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Collections;

use Countable;
use IteratorAggregate;
use OpenMetrics\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetrics\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetrics\Exposition\Text\Types\Label;
use Traversable;
use function array_map;
use function count;
use function implode;

final class LabelCollection implements Countable, IteratorAggregate
{
	/** @var array|ProvidesNamedValue[] */
	private $labels;

	private function __construct()
	{
		$this->labels = [];
	}

	public static function new() : self
	{
		return new self();
	}

	public static function fromLabels( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : self
	{
		$collection = new self();
		$collection->add( $label, ...$labels );

		return $collection;
	}

	/**
	 * @param array $labels
	 *
	 * @throws InvalidArgumentException
	 * @return LabelCollection
	 */
	public static function fromAssocArray( array $labels ) : self
	{
		$collection = self::new();

		foreach ( $labels as $name => $value )
		{
			$collection->add( Label::fromNameAndValue( $name, $value ) );
		}

		return $collection;
	}

	public function add( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : void
	{
		$this->labels[ $label->getName() ] = $label;

		foreach ( $labels as $loopLabel )
		{
			$this->labels[ $loopLabel->getName() ] = $loopLabel;
		}
	}

	public function getIterator() : Traversable
	{
		yield from $this->labels;
	}

	public function count() : int
	{
		return count( $this->labels );
	}

	public function getCombinedLabelString() : string
	{
		if ( 0 === $this->count() )
		{
			return '';
		}

		$labelStrings = array_map(
			function ( ProvidesNamedValue $label )
			{
				return $label->getLabelString();
			},
			$this->labels
		);

		return '{' . implode( ', ', $labelStrings ) . '}';
	}
}