<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Interfaces;

use Countable;

interface CollectsMetrics extends ProvidesMetricLines, Countable
{
	public static function withMetricName( NamesMetric $metricName );

	public function getMetricName() : NamesMetric;
}