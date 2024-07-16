<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\Collections;

use OpenMetricsPhp\Exposition\Text\Collections\LabelCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetricsPhp\Exposition\Text\Types\Label;
use PHPUnit\Framework\TestCase;

final class LabelCollectionTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCreateNewInstance() : void
	{
		$collection = LabelCollection::new();

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCreateNewInstanceFromLabels() : void
	{
		$labels = [
			Label::fromNameAndValue( 'test_label', 'test-value' ),
			Label::fromNameAndValue( 'label_test', 'value-test' ),
		];

		$collection = LabelCollection::fromLabels( ...$labels );

		$this->assertCount( 2, $collection );
		$this->assertSame( 2, $collection->count() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCountLabelsInCollection() : void
	{
		$collection = LabelCollection::new();

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );

		/** @var ProvidesNamedValue $labelStub */
		$labelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMock();

		$collection->add( $labelStub );

		$this->assertCount( 1, $collection );
		$this->assertSame( 1, $collection->count() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetLabelsAsCombinedLabelString() : void
	{
		$collection = LabelCollection::new();

		$this->assertSame( '', $collection->getCombinedLabelString() );

		$firstLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMock();
		$firstLabelStub->method( 'getName' )->willReturn( 'name1' );
		$firstLabelStub->method( 'getLabelString' )->willReturn( 'name1="value1"' );

		/** @var ProvidesNamedValue $firstLabelStub */
		$collection->add( $firstLabelStub );

		$this->assertSame( '{name1="value1"}', $collection->getCombinedLabelString() );

		$secondLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMock();
		$secondLabelStub->method( 'getName' )->willReturn( 'name2' );
		$secondLabelStub->method( 'getLabelString' )->willReturn( 'name2="value2"' );

		/** @var ProvidesNamedValue $secondLabelStub */
		$collection->add( $secondLabelStub );

		$this->assertSame( '{name1="value1",name2="value2"}', $collection->getCombinedLabelString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testLabelWithSameNameOverwritesPreviousLabel() : void
	{
		$collection = LabelCollection::new();

		$labelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMock();
		$labelStub->method( 'getName' )->willReturn( 'name1' );
		$labelStub->method( 'getLabelString' )->willReturn( 'name1="value1"' );

		/** @var ProvidesNamedValue $labelStub */
		$collection->add( $labelStub );

		$this->assertSame( '{name1="value1"}', $collection->getCombinedLabelString() );

		$overwriteLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMock();
		$overwriteLabelStub->method( 'getName' )->willReturn( 'name1' );
		$overwriteLabelStub->method( 'getLabelString' )->willReturn( 'name1="value2"' );

		/** @var ProvidesNamedValue $overwriteLabelStub */
		$collection->add( $overwriteLabelStub );

		$this->assertSame( '{name1="value2"}', $collection->getCombinedLabelString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanAddMultipleLabels() : void
	{
		$collection = LabelCollection::new();

		$this->assertSame( '', $collection->getCombinedLabelString() );

		$firstLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMock();
		$firstLabelStub->method( 'getName' )->willReturn( 'name1' );
		$firstLabelStub->method( 'getLabelString' )->willReturn( 'name1="value1"' );

		$secondLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMock();
		$secondLabelStub->method( 'getName' )->willReturn( 'name2' );
		$secondLabelStub->method( 'getLabelString' )->willReturn( 'name2="value2"' );

		/** @var ProvidesNamedValue $firstLabelStub */
		/** @var ProvidesNamedValue $secondLabelStub */
		$collection->add( $firstLabelStub, $secondLabelStub );

		$this->assertCount( 2, $collection );
		$this->assertSame( '{name1="value1",name2="value2"}', $collection->getCombinedLabelString() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceFromAssocArray() : void
	{
		$labels = LabelCollection::fromAssocArray(
			[
				'unit' => 'test',
				'test' => 'unit',
			]
		);

		$this->assertCount( 2, $labels );

		$expectedLabelString = '{unit="test",test="unit"}';

		$this->assertSame( $expectedLabelString, $labels->getCombinedLabelString() );
	}
}
