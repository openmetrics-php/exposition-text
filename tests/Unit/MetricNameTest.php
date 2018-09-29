<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\MetricName;
use OpenMetricsPhp\Exposition\Text\Tests\Traits\EmptyStringProviding;
use PHPUnit\Framework\TestCase;

final class MetricNameTest extends TestCase
{
	use EmptyStringProviding;

	/**
	 * @param string $metricName
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 *
	 * @dataProvider invalidMetricNameProvider
	 */
	public function testThrowsExceptionForInvalidMetricName( string $metricName ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid metric name' );

		MetricName::fromString( $metricName );

		$this->fail( 'Expected an InvalidArgumentException to be thrown for invalid metric name.' );
	}

	public function invalidMetricNameProvider() : iterable
	{
		yield from $this->emptyStringProvider();

		yield from [
			[
				'metricName' => 'metric name with whitespaces',
			],
			[
				'metricName' => 'metric-name-with-dashes',
			],
			[
				'metricName' => 'metric_name_with_$pecialChar',
			],
			[
				'metricName' => '0_metric_name_with_leading_number',
			],
		];
	}

	/**
	 * @param string $metricName
	 * @param string $expectedString
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider validMetricNameProvider
	 */
	public function testCanGetMetricNameAsString( string $metricName, string $expectedString ) : void
	{
		$metricNameObject = MetricName::fromString( $metricName );

		$this->assertSame( $expectedString, $metricNameObject->toString() );
	}

	public function validMetricNameProvider() : array
	{
		return [
			[
				'metricName'     => 'metric_name',
				'expectedString' => 'metric_name',
			],
			[
				'metricName'     => ' metric_name ',
				'expectedString' => 'metric_name',
			],
			[
				'metricName'     => 'Metric:name_with_trailing_number_123',
				'expectedString' => 'Metric:name_with_trailing_number_123',
			],
		];
	}

	/**
	 * @param string $name
	 * @param string $otherName
	 * @param bool   $expectedResult
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider equalityMetricNameProvider
	 */
	public function testCanCheckIfMetricNamesAreEqual( string $name, string $otherName, bool $expectedResult ) : void
	{
		$metricName = MetricName::fromString( $name );
		$other      = MetricName::fromString( $otherName );

		$this->assertSame( $expectedResult, $metricName->equals( $other ) );
		$this->assertSame( $expectedResult, $other->equals( $metricName ) );
	}

	public function equalityMetricNameProvider() : array
	{
		return [
			[
				'name'           => 'unit_test_metric',
				'otherName'      => 'unit_test_metric',
				'expectedResult' => true,
			],
			[
				'name'           => 'unit_test_metric ',
				'otherName'      => ' unit_test_metric',
				'expectedResult' => true,
			],
			[
				'name'           => 'unit_test_metric',
				'otherName'      => 'test_unit_metric',
				'expectedResult' => false,
			],
		];
	}
}
