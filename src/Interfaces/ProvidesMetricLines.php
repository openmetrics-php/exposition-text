<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

use Iterator;

interface ProvidesMetricLines
{
	public function getMetricLines() : Iterator;
}