<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Metrics\Summary;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Metrics\Summary\Quantile;
use PHPUnit\Framework\TestCase;

final class QuantileTest extends TestCase
{
	/**
	 * @throws InvalidArgumentException
	 */
	public function testNewThrowsExceptionForInvalidQuantile() : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid value for quantile; must be 0 <= φ <= 1' );

		/** @noinspection UnusedFunctionResultInspection */
		Quantile::new( -0.1, 1.0 );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetSampleString() : void
	{
		$quantile = Quantile::new( 0.1, 1.0 );

		$this->assertSame( '{quantile="0.1"} 1.000000', $quantile->getSampleString() );
	}
}
