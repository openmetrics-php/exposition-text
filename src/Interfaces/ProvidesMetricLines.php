<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

use Traversable;

interface ProvidesMetricLines
{
	public function getMetricLines() : Traversable;

	public function getMetricsString() : string;
}