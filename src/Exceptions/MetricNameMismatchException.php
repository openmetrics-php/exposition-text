<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Exceptions;

use OpenMetricsPhp\Exposition\Text\Interfaces\NamesMetric;

final class MetricNameMismatchException extends InvalidArgumentException
{
	public static function forCollectionItem( NamesMetric $collectionMetricName, NamesMetric $itemMetricName ) : self
	{
		return new self(
			sprintf(
				"Metric name in collection '%s' doesn't match metric name from item '%s'.",
				$collectionMetricName->toString(),
				$itemMetricName->toString()
			)
		);
	}
}