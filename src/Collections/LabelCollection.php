<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Collections;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\CollectsLabels;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use Traversable;
use function array_map;
use function count;
use function implode;

final class LabelCollection implements CollectsLabels
{
	/** @var array<ProvidesNamedValue> */
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
	 * @param array<string, string> $labels
	 *
	 * @return LabelCollection
	 * @throws InvalidArgumentException
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
			static function ( ProvidesNamedValue $label )
			{
				return $label->getLabelString();
			},
			$this->labels
		);

		return '{' . implode( ',', $labelStrings ) . '}';
	}
}
