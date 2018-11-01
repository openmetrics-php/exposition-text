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
	 */
	public function testCanGetInstanceFromValueAndTimestamp() : void
	{
		$timestamp      = time();
		$expectedSample = ' 1 ' . $timestamp;

		$gauge = Counter::fromValueAndTimestamp( 1, $timestamp );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testFromMetricNameAndValue() : void
	{
		$expectedSample = ' 1';

		$gauge = Counter::fromValue( 1 );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetSampleString() : void
	{
		$timestamp                            = time();
		$expectedSampleStringWithTimestamp    = ' 1 ' . $timestamp;
		$expectedSampleStringWithoutTimestamp = ' 1';

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
		$expectedSampleStringWithoutLabels   = ' 1';
		$expectedSampleStringWithOneLabel    = '{unit_test="123"} 1';
		$expectedSampleStringWithThreeLabels = '{unit_test="123", test_unit="456", label:last="789"} 1';

		$gauge = Counter::fromValue( 1 );

		$this->assertSame( $expectedSampleStringWithoutLabels, $gauge->getSampleString() );

		$gauge->addLabels( Label::fromNameAndValue( 'unit_test', '123' ) );

		$this->assertSame( $expectedSampleStringWithOneLabel, $gauge->getSampleString() );

		$gauge->addLabels(
			Label::fromNameAndValue( 'test_unit', '456' ),
			Label::fromNameAndValue( 'label:last', '789' )
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
		$gauge = Counter::fromValue( 12 )
		                ->withLabels(
			                Label::fromNameAndValue( 'unit', 'test' ),
			                Label::fromNameAndValue( 'test', 'unit' )
		                );

		$expectedSampleString = '{unit="test", test="unit"} 12';

		$this->assertSame( $expectedSampleString, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetCounterWithLabelCollection() : void
	{
		$gauge = Counter::fromValue( 12 )
		                ->withLabelCollection(
			                LabelCollection::fromAssocArray(
				                [
					                'unit' => 'test',
					                'test' => 'unit',
				                ]
			                )
		                );

		$expectedSampleString = '{unit="test", test="unit"} 12';

		$this->assertSame( $expectedSampleString, $gauge->getSampleString() );
	}
}
