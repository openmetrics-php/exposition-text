<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text;

use Countable;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use function array_map;
use function count;
use function implode;

final class LabelCollection implements Countable
{
	/** @var array|ProvidesNamedValue[] */
	private $labels = [];

	public function count() : int
	{
		return count( $this->labels );
	}

	public function add( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : void
	{
		$this->labels[ $label->getName() ] = $label;

		foreach ( $labels as $loopLabel )
		{
			$this->labels[ $loopLabel->getName() ] = $loopLabel;
		}
	}

	public function asCombinedLabelString() : string
	{
		if ( 0 === $this->count() )
		{
			return '';
		}

		$labelStrings = array_map(
			function ( ProvidesNamedValue $label )
			{
				return $label->asLabelString();
			},
			$this->labels
		);

		return '{' . implode( ', ', $labelStrings ) . '}';
	}
}