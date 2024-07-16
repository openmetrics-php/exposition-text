<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use PHPUnit\Framework\TestCase;

final class CounterTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetInstanceFromValueAndTimestamp() : void
	{
		$timestamp      = time();
		$expectedSample = '_total 1.000000 ' . $timestamp;

		$gauge = Counter::fromValueAndTimestamp( 1, $timestamp );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws InvalidArgumentException
	 */
	public function testFromMetricNameAndValue() : void
	{
		$expectedSample = '_total 1.000000';

		$gauge = Counter::fromValue( 1 );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws InvalidArgumentException
	 */
	public function testGetSampleString() : void
	{
		$timestamp                            = time();
		$expectedSampleStringWithTimestamp    = '_total 1.000000 ' . $timestamp;
		$expectedSampleStringWithoutTimestamp = '_total 1.000000';

		$gauge = Counter::fromValueAndTimestamp( 1, $timestamp );

		$this->assertSame( $expectedSampleStringWithTimestamp, $gauge->getSampleString() );

		$gauge = Counter::fromValue( 1 );

		$this->assertSame( $expectedSampleStringWithoutTimestamp, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testAddLabels() : void
	{
		$expectedSampleStringWithoutLabels   = '_total 1.000000';
		$expectedSampleStringWithOneLabel    = '_total{unit_test="123"} 1.000000';
		$expectedSampleStringWithThreeLabels = '_total{unit_test="123",test_unit="456",label_last="789"} 1.000000';

		$gauge = Counter::fromValue( 1 );

		$this->assertSame( $expectedSampleStringWithoutLabels, $gauge->getSampleString() );

		$gauge->addLabels( Label::fromNameAndValue( 'unit_test', '123' ) );

		$this->assertSame( $expectedSampleStringWithOneLabel, $gauge->getSampleString() );

		$gauge->addLabels(
			Label::fromNameAndValue( 'test_unit', '456' ),
			Label::fromNameAndValue( 'label_last', '789' )
		);

		$this->assertSame( $expectedSampleStringWithThreeLabels, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetCounterWithLabels() : void
	{
		$gauge = Counter::fromValue( 12.3 )
		                ->withLabels(
			                Label::fromNameAndValue( 'unit', 'test' ),
			                Label::fromNameAndValue( 'test', 'unit' )
		                );

		$expectedSampleString = '_total{unit="test",test="unit"} 12.300000';

		$this->assertSame( $expectedSampleString, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetCounterWithLabelCollection() : void
	{
		$gauge = Counter::fromValue( 12.3 )
		                ->withLabelCollection(
			                LabelCollection::fromAssocArray(
				                [
					                'unit' => 'test',
					                'test' => 'unit',
				                ]
			                )
		                );

		$expectedSampleString = '_total{unit="test",test="unit"} 12.300000';

		$this->assertSame( $expectedSampleString, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function testThrowsExceptionForNegativeCounterValue() : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Counters must start at 0 and can only go up.' );

		/** @noinspection UnusedFunctionResultInspection */
		Counter::fromValue( -0.123 );
	}
}
