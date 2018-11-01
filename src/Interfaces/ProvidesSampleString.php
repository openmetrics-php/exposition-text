<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

interface ProvidesSampleString
{
	public function getSampleString() : string;
}