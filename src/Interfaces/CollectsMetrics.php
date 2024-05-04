<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

use Countable;

interface CollectsMetrics extends ProvidesMetricLines, Countable
{
	/**
	 * @param NamesMetric $metricName
	 *
	 * @return CollectsMetrics
	 */
	public static function withMetricName( NamesMetric $metricName );

	public function getMetricName() : NamesMetric;
}