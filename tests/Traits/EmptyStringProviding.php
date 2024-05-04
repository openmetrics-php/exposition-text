<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Traits;

trait EmptyStringProviding
{
	public static function emptyStringProvider() : array
	{
		return [
			[
				'string' => '',
			],
			[
				'string' => ' ',
			],
			[
				'string' => "\n",
			],
			[
				'string' => "\r",
			],
			[
				'string' => "\0",
			],
		];
	}
}