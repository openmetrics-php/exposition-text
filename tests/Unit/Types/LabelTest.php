<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Types;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Tests\Traits\EmptyStringProviding;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use PHPUnit\Framework\TestCase;
use Traversable;

final class LabelTest extends TestCase
{
	use EmptyStringProviding;

	/**
	 * @param string $string
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 *
	 * @dataProvider emptyStringProvider
	 */
	#[DataProvider('emptyStringProvider')]
	public function testThrowsExceptionForEmptyValue( string $string ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Label value cannot be empty.' );

		/** @noinspection UnusedFunctionResultInspection */
		Label::fromNameAndValue( 'name', $string );

		$this->fail( 'Expected an InvalidArgumentException to be thrown for an empty label value.' );
	}

	/**
	 * @param string $name
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 *
	 * @dataProvider invalidLabelNameProvider
	 */
	public function testThrowsExceptionForInvalidLabelName( string $name ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid label name.' );

		/** @noinspection UnusedFunctionResultInspection */
		Label::fromNameAndValue( $name, 'value' );

		$this->fail( 'Expected an InvalidArgumentException to be thrown for invalid label name.' );
	}

	public static function invalidLabelNameProvider() : Traversable
	{
		yield from array_map(
			static function ( array $record ) : array
			{
				return ['name' => $record['string']];
			},
			self::emptyStringProvider()
		);

		yield from [
			[
				'name' => 'label with whitespace',
			],
			[
				'name' => 'label-with-dashes',
			],
			[
				'name' => 'label_with,comma',
			],
			[
				'name' => "label-with\ttab",
			],
			[
				'name' => "label-with\nlinebreak",
			],
			[
				'name' => 'label-with:colon',
			],
		];
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $expectedLabelString
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider labelStringsProvider
	 */
	public function testCanGetLabelString( string $name, string $value, string $expectedLabelString ) : void
	{
		$label = Label::fromNameAndValue( $name, $value );

		$this->assertSame( trim( $name ), $label->getName() );
		$this->assertSame( trim( $value ), $label->getValue() );
		$this->assertSame( $expectedLabelString, $label->getLabelString() );
	}

	public static function labelStringsProvider() : array
	{
		return [
			[
				'name'                => 'unit',
				'value'               => 'test',
				'expectedLabelString' => 'unit="test"',
			],
			[
				'name'                => 'name',
				'value'               => 'value with whitespaces',
				'expectedLabelString' => 'name="value with whitespaces"',
			],
			[
				'name'                => ' name_with_surrounding_whitespaces ',
				'value'               => ' value with surrounding whitespaces ',
				'expectedLabelString' => 'name_with_surrounding_whitespaces="value with surrounding whitespaces"',
			],
			[
				'name'                => 'name',
				'value'               => 'value with "',
				'expectedLabelString' => 'name="value with \""',
			],
			[
				'name'                => 'name',
				'value'               => 'value with \\',
				'expectedLabelString' => 'name="value with \\\"',
			],
			[
				'name'                => 'name',
				'value'               => "value with\nlinebreak",
				'expectedLabelString' => 'name="value with\nlinebreak"',
			],
			[
				'name'                => 'name_with_underscore',
				'value'               => 'value',
				'expectedLabelString' => 'name_with_underscore="value"',
			],
			[
				'name'                => 'name_with_0123',
				'value'               => 'value',
				'expectedLabelString' => 'name_with_0123="value"',
			],
		];
	}

	/**
	 * @param string $labelString
	 * @param string $expectedName
	 * @param string $expectedValue
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider labelStringToNameValueProvider
	 */
	public function testCanGetNameAndValueFromLabelString(
		string $labelString,
		string $expectedName,
		string $expectedValue
	) : void
	{
		$label = Label::fromLabelString( $labelString );

		$this->assertSame( $expectedName, $label->getName() );
		$this->assertSame( $expectedValue, $label->getValue() );
	}

	public static function labelStringToNameValueProvider() : array
	{
		return [
			[
				'labelString'   => 'unit="test"',
				'expectedName'  => 'unit',
				'expectedValue' => 'test',
			],
			[
				'labelString'   => 'name="value with whitespaces"',
				'expectedName'  => 'name',
				'expectedValue' => 'value with whitespaces',
			],
			[
				'labelString'   => 'name="value with \""',
				'expectedName'  => 'name',
				'expectedValue' => 'value with "',
			],
			[
				'labelString'   => 'name="value with \\\"',
				'expectedName'  => 'name',
				'expectedValue' => 'value with \\',
			],
			[
				'labelString'   => 'name="value with\nlinebreak"',
				'expectedName'  => 'name',
				'expectedValue' => "value with\nlinebreak",
			],
		];
	}

	/**
	 * @param string $labelString
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 *
	 * @dataProvider invalidLabelStringProvider
	 */
	public function testThrowsExceptionForInvalidLabelStrings( string $labelString ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid label string.' );

		/** @noinspection UnusedFunctionResultInspection */
		Label::fromLabelString( $labelString );

		$this->fail( 'Expected exception for invalid label string.' );
	}

	public static function invalidLabelStringProvider() : array
	{
		return [
			[
				'labelString' => 'name=value',
			],
			[
				'labelString' => 'name and="value"',
			],
		];
	}
}
