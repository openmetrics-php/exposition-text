<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Interfaces;

use Iterator;

interface ProvidesMetricLines
{
	public function getMetricLines() : Iterator;

	public function getMetricsString() : string;
}