<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Metrics;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use PHPUnit\Framework\TestCase;

final class GaugeTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceFromValueAndTimestamp() : void
	{
		$timestamp      = time();
		$expectedSample = ' 1.230000 ' . $timestamp;

		$gauge = Gauge::fromValueAndTimestamp( 1.23, $timestamp );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testFromMetricNameAndValue() : void
	{
		$expectedSample = ' 1.230000';

		$gauge = Gauge::fromValue( 1.23 );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetSampleString() : void
	{
		$timestamp                            = time();
		$expectedSampleStringWithTimestamp    = ' 1.230000 ' . $timestamp;
		$expectedSampleStringWithoutTimestamp = ' 1.230000';

		$gauge = Gauge::fromValueAndTimestamp( 1.23, $timestamp );

		$this->assertSame( $expectedSampleStringWithTimestamp, $gauge->getSampleString() );

		$gauge = Gauge::fromValue( 1.23 );

		$this->assertSame( $expectedSampleStringWithoutTimestamp, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testAddLabels() : void
	{
		$expectedSampleStringWithoutLabels   = ' 1.230000';
		$expectedSampleStringWithOneLabel    = '{unit_test="123"} 1.230000';
		$expectedSampleStringWithThreeLabels = '{unit_test="123",test_unit="456",label_last="789"} 1.230000';

		$gauge = Gauge::fromValue( 1.23 );

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
	public function testCanGetGaugeWithLabels() : void
	{
		$gauge = Gauge::fromValue(
			12.3
		)->withLabels(
			Label::fromNameAndValue( 'unit', 'test' ),
			Label::fromNameAndValue( 'test', 'unit' )
		);

		$expectedSampleString = '{unit="test",test="unit"} 12.300000';

		$this->assertSame( $expectedSampleString, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetGaugeWithLabelCollection() : void
	{
		$gauge = Gauge::fromValue(
			12.3
		)->withLabelCollection(
			LabelCollection::fromAssocArray(
				[
					'unit' => 'test',
					'test' => 'unit',
				]
			)
		);

		$expectedSampleString = '{unit="test",test="unit"} 12.300000';

		$this->assertSame( $expectedSampleString, $gauge->getSampleString() );
	}
}
