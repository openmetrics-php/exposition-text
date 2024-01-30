<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text;

use OpenMetricsPhp\Exposition\Text\HttpResponse\OutputStream;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesMetricLines;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Traversable;
use function is_array;

final class HttpResponse implements ResponseInterface
{
	/** @var string */
	private $protocolVersion;

	/** @var array */
	private $headers;

	/** @var int */
	private $statusCode;

	/** @var string */
	private $reasonPhrase;

	/** @var StreamInterface */
	private $body;

	private function __construct( StreamInterface $body )
	{
		$this->protocolVersion = '1.1';
		$this->headers         = ['Content-Type' => ['application/openmetrics-text; charset=utf-8']];
		$this->statusCode      = 200;
		$this->reasonPhrase    = 'OK';
		$this->body            = $body;
	}

	/**
	 * @param ProvidesMetricLines $collection
	 * @param ProvidesMetricLines ...$collections
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\RuntimeException
	 * @return HttpResponse
	 */
	public static function fromMetricCollections(
		ProvidesMetricLines $collection,
		ProvidesMetricLines ...$collections
	) : self
	{
		$outputStream = new OutputStream( 'php://temp', 'w+b' );

		foreach ( self::getAllMetricLines( $collection, ...$collections ) as $line )
		{
			$outputStream->write( $line . "\n" );
		}

		return new self( $outputStream );
	}

	private static function getAllMetricLines(
		ProvidesMetricLines $collection,
		ProvidesMetricLines ...$collections
	) : Traversable
	{
		yield from $collection->getMetricLines();
		foreach ( $collections as $loopCollection )
		{
			yield from $loopCollection->getMetricLines();
		}
	}

	public function getProtocolVersion() : string
	{
		return $this->protocolVersion;
	}

	public function withProtocolVersion( $version ) : self
	{
		$response                  = clone $this;
		$response->protocolVersion = (string)$version;

		return $response;
	}

	public function getHeaders() : array
	{
		return $this->headers;
	}

	public function hasHeader( $name ) : bool
	{
		return isset( $this->headers[ (string)$name ] );
	}

	public function getHeader( $name ) : array
	{
		return $this->headers[ (string)$name ] ?? [];
	}

	public function getHeaderLine( $name ) : string
	{
		if ( $this->hasHeader( $name ) )
		{
			return implode( ',', $this->getHeader( $name ) );
		}

		return '';
	}

	public function withHeader( $name, $value )
	{
		$this->headers[ $name ] = !is_array( $value ) ? [$value] : $value;

		return $this;
	}

	public function withAddedHeader( $name, $value ) : self
	{
		return (clone $this)->withHeader( $name, $value );
	}

	public function withoutHeader( $name ) : self
	{
		$response = clone $this;
		unset( $response->headers[ (string)$name ] );

		return $response;
	}

	public function getBody() : StreamInterface
	{
		return $this->body;
	}

	public function withBody( StreamInterface $body ) : self
	{
		$response       = clone $this;
		$response->body = $body;

		return $response;
	}

	public function getStatusCode() : int
	{
		return $this->statusCode;
	}

	public function withStatus( $code, $reasonPhrase = '' ) : self
	{
		$response               = clone $this;
		$response->statusCode   = $code;
		$response->reasonPhrase = $reasonPhrase;

		return $response;
	}

	public function getReasonPhrase() : string
	{
		return $this->reasonPhrase;
	}

	public function respond() : void
	{
		foreach ( array_keys( $this->headers ) as $name )
		{
			header( $name . ': ' . $this->getHeaderLine( $name ), true, $this->statusCode );
		}

		echo $this->body;
		flush();
	}
}