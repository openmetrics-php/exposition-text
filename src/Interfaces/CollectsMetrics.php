<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

use Countable;
use Iterator;

interface CollectsMetrics extends Countable
{
	public static function withMetricName( NamesMetric $metricName );

	public function getMetricName() : NamesMetric;

	public function getMetricLines() : Iterator;

	public function getMetricsString() : string;
}