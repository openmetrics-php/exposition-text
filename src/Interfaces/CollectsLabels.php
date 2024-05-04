<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

use Countable;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<ProvidesNamedValue>
 */
interface CollectsLabels extends Countable, IteratorAggregate
{
	public function add( ProvidesNamedValue $label, ProvidesNamedValue ...$labels ) : void;

	public function getCombinedLabelString() : string;
}