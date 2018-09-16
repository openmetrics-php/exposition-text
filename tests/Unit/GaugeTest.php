<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Gauge;
use OpenMetricsPhp\Exposition\Text\Label;
use PHPUnit\Framework\TestCase;

final class GaugeTest extends TestCase
{
	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceFromMetricNameValueAndTimestamp() : void
	{
		$timestamp      = time();
		$expectedSample = 'unit_test_metric 1.230000 ' . $timestamp;

		$gauge = Gauge::fromMetricNameValueAndTimestamp( 'unit_test_metric', 1.23, $timestamp );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testFromMetricNameAndValue() : void
	{
		$expectedSample = 'unit_test_metric 1.230000';

		$gauge = Gauge::fromMetricNameAndValue( 'unit_test_metric', 1.23 );

		$this->assertSame( $expectedSample, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testSetHelp() : void
	{
		$gauge = Gauge::fromMetricNameAndValue( 'unit_test_metric', 1.23 );

		$this->assertSame( '', $gauge->getHelpString() );

		$gauge->setHelp( 'This helps understanding the metric.' );

		$this->assertSame( '# HELP unit_test_metric This helps understanding the metric.', $gauge->getHelpString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetTypeString() : void
	{
		$gauge = Gauge::fromMetricNameAndValue( 'unit_test_metric', 1.23 );

		$this->assertSame( '# TYPE unit_test_metric gauge', $gauge->getTypeString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetSingleMetricString() : void
	{
		$timestamp            = time();
		$expectedHelpString   = '# HELP unit_test_metric This helps understaning the metric.';
		$expectedTypeString   = '# TYPE unit_test_metric gauge';
		$expectedSampleString = 'unit_test_metric 1.230000 ' . $timestamp;

		$gauge = Gauge::fromMetricNameValueAndTimestamp( 'unit_test_metric', 1.23, $timestamp );

		$this->assertSame( $expectedTypeString . "\n" . $expectedSampleString, $gauge->getSingleMetricString() );

		$gauge->setHelp( 'This helps understaning the metric.' );

		$this->assertSame(
			$expectedHelpString . "\n"
			. $expectedTypeString . "\n"
			. $expectedSampleString,
			$gauge->getSingleMetricString()
		);
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetSampleString() : void
	{
		$timestamp                            = time();
		$expectedSampleStringWithTimestamp    = 'unit_test_metric 1.230000 ' . $timestamp;
		$expectedSampleStringWithoutTimestamp = 'unit_test_metric 1.230000';

		$gauge = Gauge::fromMetricNameValueAndTimestamp( 'unit_test_metric', 1.23, $timestamp );

		$this->assertSame( $expectedSampleStringWithTimestamp, $gauge->getSampleString() );

		$gauge = Gauge::fromMetricNameAndValue( 'unit_test_metric', 1.23 );

		$this->assertSame( $expectedSampleStringWithoutTimestamp, $gauge->getSampleString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testAddLabels() : void
	{
		$expectedSampleStringWithoutLabels   = 'unit_test_metric 1.230000';
		$expectedSampleStringWithOneLabel    = 'unit_test_metric{unit_test="123"} 1.230000';
		$expectedSampleStringWithThreeLabels = 'unit_test_metric{unit_test="123", test_unit="456", label:last="789"} 1.230000';

		$gauge = Gauge::fromMetricNameAndValue( 'unit_test_metric', 1.23 );

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
	public function testGetHelpString() : void
	{
		$gauge = Gauge::fromMetricNameAndValue( 'unit_test_metric', 1.23 );

		$this->assertSame( '', $gauge->getHelpString() );

		$gauge->setHelp( 'This helps understanding the metric.' );

		$this->assertSame( '# HELP unit_test_metric This helps understanding the metric.', $gauge->getHelpString() );
	}
}
