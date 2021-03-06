<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit\HttpResponse;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Exceptions\RuntimeException;
use OpenMetricsPhp\Exposition\Text\HttpResponse\OutputStream;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;
use function tempnam;

final class OutputStreamTest extends TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testRead() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );
		$stream->rewind();

		$this->assertSame( 'Unit-Test', $stream->read( 1024 ) );

		$stream->close();
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testReadThrowsExceptionWhenNoResourceIsAvailable() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->close();

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'No resource available; cannot read' );

		/** @noinspection UnusedFunctionResultInspection */
		$stream->read( 1 );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testReadThrowsExceptionForNonReadableStreams() : void
	{
		$nonReadableStream = new OutputStream( 'php://output', 'wb' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Stream is not readable' );

		/** @noinspection UnusedFunctionResultInspection */
		$nonReadableStream->read( 1 );
	}

	/**
	 * @throws RuntimeException
	 * @throws InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testSeek() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );
		$stream->seek( 5 );

		$this->assertSame( 'Test', $stream->read( 1024 ) );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testSeekThrowsExceptionIfNoResourceIsAvailable() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->close();

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'No resource available; cannot seek position' );

		$stream->seek( 0 );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testSeekThrowsExceptionOnErrorSeekingWithinStream() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Error seeking within stream' );

		$stream->seek( 1 );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testSeekThrowsExceptionForNonSeekableStreams() : void
	{
		$stream = new OutputStream( 'php://output', 'wb' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Stream is not seekable' );

		$stream->seek( 1 );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testGetSize() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );

		$this->assertSame( 9, $stream->getSize() );

		$stream->close();

		$this->assertNull( $stream->getSize() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetMetadata() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );

		$expectedMetaData = [
			'eof'          => false,
			'unread_bytes' => 0,
			'seekable'     => true,
			'uri'          => 'php://memory',
			'timed_out'    => false,
			'blocked'      => true,
			'wrapper_type' => 'PHP',
			'stream_type'  => 'MEMORY',
			'mode'         => 'w+b',
		];

		$this->assertEquals( $expectedMetaData, $stream->getMetadata() );
		$this->assertSame( 'PHP', $stream->getMetadata( 'wrapper_type' ) );
		$this->assertNull( $stream->getMetadata( 'no-meta-data-key' ) );

		$stream->close();

		$this->assertNull( $stream->getMetadata() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function test__toString() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );

		$this->assertSame( 'Unit-Test', (string)$stream );
		$this->assertSame( 'Unit-Test', $stream->__toString() );

		$nonReadableStream = new OutputStream( 'php://stdout', 'wb' );
		$nonReadableStream->write( 'Unit-Test' );

		$this->assertSame( '', (string)$nonReadableStream );
		$this->assertSame( '', $nonReadableStream->__toString() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testEof() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );
		$stream->rewind();

		$this->assertFalse( $stream->eof() );

		while ( !$stream->eof() )
		{
			$stream->read( 11 );
		}

		$this->assertTrue( $stream->eof() );

		$stream->close();

		$this->assertTrue( $stream->eof() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testIsWritable() : void
	{
		$tempFile       = tempnam( sys_get_temp_dir(), 'Unit-Test-OutputStream-' );
		$writableStream = new OutputStream( $tempFile, 'w+b' );

		$this->assertTrue( $writableStream->isWritable() );

		$writableStream->close();

		$this->assertFalse( $writableStream->isWritable() );

		$nonWritableStream = new OutputStream( 'php://input', 'rb' );

		$this->assertFalse( $nonWritableStream->isWritable() );

		@unlink( $tempFile );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testRewind() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );

		$this->assertSame( 9, $stream->tell() );

		$stream->rewind();

		$this->assertSame( 0, $stream->tell() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testClose() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->close();

		$this->assertNull( $stream->detach() );

		$stream->close();

		$this->assertNull( $stream->detach() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testTell() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );

		$this->assertSame( 9, $stream->tell() );

		$stream->seek( 3 );

		$this->assertSame( 3, $stream->tell() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testTellThrowsExceptionIfNoResourceIsAvailable() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->close();

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'No resource available; cannot tell position' );

		$stream->tell();
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testDetach() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );

		$resource = $stream->detach();

		$this->assertIsResource( $resource );

		$this->assertNull( $stream->detach() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testGetContents() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );

		$stream->seek( 5 );

		$this->assertSame( 'Test', $stream->getContents() );

		$stream->rewind();

		$this->assertSame( 'Unit-Test', $stream->getContents() );

		$stream->close();

		$this->assertSame( '', $stream->getContents() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testIsReadable() : void
	{
		$readableStream = new OutputStream( 'php://memory', 'w+b' );

		$this->assertTrue( $readableStream->isReadable() );

		$readableStream->close();

		$this->assertFalse( $readableStream->isReadable() );

		$nonReadableStream = new OutputStream( 'php://output', 'wb' );

		$this->assertFalse( $nonReadableStream->isReadable() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testWrite() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );

		$this->assertSame( 9, $stream->write( 'Unit-Test' ) );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testWriteThrowsExceptionIfNoResourceIsAvailable() : void
	{
		$stream = new OutputStream( 'php://memory', 'w+b' );
		$stream->close();

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'No resource available; cannot write' );

		$stream->write( 'Unit-Test' );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testIsSeekable() : void
	{
		$seekableStream = new OutputStream( 'php://input', 'rb' );

		$this->assertTrue( $seekableStream->isSeekable() );

		$seekableStream->close();

		$this->assertFalse( $seekableStream->isSeekable() );
	}

	/**
	 * @throws \InvalidArgumentException
	 * @throws ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanConstructStreamFromResource() : void
	{
		$resource = fopen( 'php://memory', 'w+b' );
		$stream   = new OutputStream( $resource );

		$this->assertSame( $resource, $stream->detach() );
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function testThrowsExceptionForInvalidStream() : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			'Invalid file provided for stream; must be a valid path with valid permissions'
		);

		new OutputStream( 'file:///does/not/exist' );
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function testThrowsExceptionForInvalidStreamType() : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			'Invalid stream provided; must be a string stream identifier or stream resource'
		);

		/** @noinspection PhpParamsInspection */
		new OutputStream( ['stream'] );
	}
}
