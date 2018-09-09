<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit;

use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use OpenMetricsPhp\Exposition\Text\LabelCollection;
use PHPUnit\Framework\TestCase;

final class LabelCollectionTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanCountLabelsInCollection() : void
	{
		$collection = new LabelCollection();

		$this->assertCount( 0, $collection );
		$this->assertSame( 0, $collection->count() );

		/** @var ProvidesNamedValue $labelStub */
		$labelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMockForAbstractClass();

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
		$collection = new LabelCollection();

		$this->assertSame( '', $collection->asCombinedLabelString() );

		$firstLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMockForAbstractClass();
		$firstLabelStub->method( 'getName' )->willReturn( 'name1' );
		$firstLabelStub->method( 'asLabelString' )->willReturn( 'name1="value1"' );

		/** @var ProvidesNamedValue $firstLabelStub */
		$collection->add( $firstLabelStub );

		$this->assertSame( '{name1="value1"}', $collection->asCombinedLabelString() );

		$secondLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMockForAbstractClass();
		$secondLabelStub->method( 'getName' )->willReturn( 'name2' );
		$secondLabelStub->method( 'asLabelString' )->willReturn( 'name2="value2"' );

		/** @var ProvidesNamedValue $secondLabelStub */
		$collection->add( $secondLabelStub );

		$this->assertSame( '{name1="value1", name2="value2"}', $collection->asCombinedLabelString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testLabelWithSameNameOverwritesPreviousLabel() : void
	{
		$collection = new LabelCollection();

		$labelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMockForAbstractClass();
		$labelStub->method( 'getName' )->willReturn( 'name1' );
		$labelStub->method( 'asLabelString' )->willReturn( 'name1="value1"' );

		/** @var ProvidesNamedValue $labelStub */
		$collection->add( $labelStub );

		$this->assertSame( '{name1="value1"}', $collection->asCombinedLabelString() );

		$overwriteLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMockForAbstractClass();
		$overwriteLabelStub->method( 'getName' )->willReturn( 'name1' );
		$overwriteLabelStub->method( 'asLabelString' )->willReturn( 'name1="value2"' );

		/** @var ProvidesNamedValue $overwriteLabelStub */
		$collection->add( $overwriteLabelStub );

		$this->assertSame( '{name1="value2"}', $collection->asCombinedLabelString() );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanAddMultipleLabels() : void
	{
		$collection = new LabelCollection();

		$this->assertSame( '', $collection->asCombinedLabelString() );

		$firstLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMockForAbstractClass();
		$firstLabelStub->method( 'getName' )->willReturn( 'name1' );
		$firstLabelStub->method( 'asLabelString' )->willReturn( 'name1="value1"' );

		$secondLabelStub = $this->getMockBuilder( ProvidesNamedValue::class )->getMockForAbstractClass();
		$secondLabelStub->method( 'getName' )->willReturn( 'name2' );
		$secondLabelStub->method( 'asLabelString' )->willReturn( 'name2="value2"' );

		/** @var ProvidesNamedValue $firstLabelStub */
		/** @var ProvidesNamedValue $secondLabelStub */
		$collection->add( $firstLabelStub, $secondLabelStub );

		$this->assertCount( 2, $collection );
		$this->assertSame( '{name1="value1", name2="value2"}', $collection->asCombinedLabelString() );
	}
}
