<?php declare(strict_types=1);

namespace OpenMetrics\Exposition\Text\Interfaces;

interface ProvidesSampleString
{
	public function getSampleString() : string;
}