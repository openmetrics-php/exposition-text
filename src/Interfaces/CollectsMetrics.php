<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

use Countable;
use Iterator;

interface CollectsMetrics extends Countable
{
	public function getMetricLines() : Iterator;

	public function getMetricsString() : string;
}