<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Tests\Unit;

use OpenMetricsPhp\Exposition\Text\Collections\CounterCollection;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\HttpResponse;
use OpenMetricsPhp\Exposition\Text\Metrics\Counter;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;
use PHPUnit\Framework\TestCase;
use function headers_list;

final class HttpResponseTest extends TestCase
{
	/**
	 * @throws \InvalidArgumentException
	 * @throws InvalidArgumentException
	 * @throws \RuntimeException
	 * @covers \OpenMetricsPhp\Exposition\Text\HttpResponse::respond()
	 * @runInSeparateProcess
	 */
	public function testCanRespondMetrics() : void
	{
		$gaugeCollection   = $this->getExampleGaugeCollection();
		$counterCollection = $this->getExampleCounterCollection();

		$expectedOutput = "# TYPE unit_test_gauges gauge\n";
		$expectedOutput .= "# HELP unit_test_gauges Test gauges\n";
		$expectedOutput .= "unit_test_gauges 12.300000\n";
		$expectedOutput .= "unit_test_gauges 45.600000\n";
		$expectedOutput .= "unit_test_gauges 78.900000\n";
		$expectedOutput .= "# TYPE unit_test_counters counter\n";
		$expectedOutput .= "# HELP unit_test_counters Test counters\n";
		$expectedOutput .= "unit_test_counters 12.300000\n";
		$expectedOutput .= "unit_test_counters 45.600000\n";
		$expectedOutput .= "unit_test_counters 78.900000\n";

		$this->expectOutputString( $expectedOutput );

		HttpResponse::fromMetricCollections( $gaugeCollection, $counterCollection )->respond();

		$headers = headers_list();

		$this->assertContains( 'Content-Type: application/openmetrics-text; charset=utf-8', $headers );
	}

	/**
	 * @throws InvalidArgumentException
	 * @return GaugeCollection
	 */
	private function getExampleGaugeCollection() : GaugeCollection
	{
		return GaugeCollection::fromGauges(
			MetricName::fromString( 'unit_test_gauges' ),
			Gauge::fromValue( 12.3 ),
			Gauge::fromValue( 45.6 ),
			Gauge::fromValue( 78.9 )
		)->withHelp(
			'Test gauges'
		);
	}

	/**
	 * @throws InvalidArgumentException
	 * @return CounterCollection
	 */
	private function getExampleCounterCollection() : CounterCollection
	{
		return CounterCollection::fromCounters(
			MetricName::fromString( 'unit_test_counters' ),
			Counter::fromValue( 12.3 ),
			Counter::fromValue( 45.6 ),
			Counter::fromValue( 78.9 )
		)->withHelp(
			'Test counters'
		);
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \RuntimeException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceWithAddedHeader() : void
	{
		$response        = HttpResponse::fromMetricCollections( $this->getExampleCounterCollection() );
		$withAddedHeader = $response->withAddedHeader( 'X-Test', 'Unit' );

		$this->assertNotSame( $withAddedHeader, $response );
		$this->assertCount( 2, $withAddedHeader->getHeaders() );
		$this->assertCount( 1, $response->getHeaders() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \InvalidArgumentException
	 * @throws \OpenMetricsPhp\Exposition\Text\Exceptions\RuntimeException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \RuntimeException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceWithBody() : void
	{
		$stream = new HttpResponse\OutputStream( 'php://memory', 'w+b' );
		$stream->write( 'Unit-Test' );

		$response = HttpResponse::fromMetricCollections( $this->getExampleCounterCollection() );
		$withBody = $response->withBody( $stream );

		$this->assertNotSame( $withBody, $response );
		$this->assertNotSame( $withBody->getBody(), $response->getBody() );
		$this->assertSame( 9, $withBody->getBody()->getSize() );
		$this->assertSame( 179, $response->getBody()->getSize() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \RuntimeException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceWithProtocolVersion() : void
	{
		$response            = HttpResponse::fromMetricCollections( $this->getExampleCounterCollection() );
		$withProtocolVersion = $response->withProtocolVersion( '1.0' );

		$this->assertNotSame( $response, $withProtocolVersion );
		$this->assertSame( '1.0', $withProtocolVersion->getProtocolVersion() );
		$this->assertSame( '1.1', $response->getProtocolVersion() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \RuntimeException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceWithoutHeader() : void
	{
		$response      = HttpResponse::fromMetricCollections( $this->getExampleCounterCollection() );
		$withoutHeader = $response->withoutHeader( 'Content-Type' );

		$this->assertNotSame( $withoutHeader, $response );
		$this->assertCount( 0, $withoutHeader->getHeaders() );
		$this->assertCount( 1, $response->getHeaders() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \RuntimeException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetInstanceWithStatus() : void
	{
		$response   = HttpResponse::fromMetricCollections( $this->getExampleCounterCollection() );
		$withStatus = $response->withStatus( 500, 'Internal Server Error' );

		$this->assertNotSame( $withStatus, $response );
		$this->assertSame( 500, $withStatus->getStatusCode() );
		$this->assertSame( 'Internal Server Error', $withStatus->getReasonPhrase() );
		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame( 'OK', $response->getReasonPhrase() );
	}

	/**
	 * @throws InvalidArgumentException
	 * @throws \InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \RuntimeException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetDefaultValues() : void
	{
		$response = HttpResponse::fromMetricCollections(
			$this->getExampleCounterCollection(),
			$this->getExampleGaugeCollection()
		);

		$this->assertSame(
			['Content-Type' => ['application/openmetrics-text; charset=utf-8']],
			$response->getHeaders()
		);

		$this->assertSame( '1.1', $response->getProtocolVersion() );
		$this->assertSame( 'OK', $response->getReasonPhrase() );
		$this->assertSame( 200, $response->getStatusCode() );
		$this->assertSame(
			'application/openmetrics-text; charset=utf-8',
			$response->getHeaderLine( 'Content-Type' )
		);
		$this->assertSame( '', $response->getHeaderLine( 'Not-existing-key' ) );
		$this->assertTrue( $response->hasHeader( 'Content-Type' ) );
	}
}
