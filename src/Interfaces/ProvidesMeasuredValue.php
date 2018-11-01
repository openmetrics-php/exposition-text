<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Interfaces;

interface ProvidesMeasuredValue
{
	public function getMeasuredValue() : float;
}