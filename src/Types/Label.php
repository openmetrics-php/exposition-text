<?php declare(strict_types=1);

namespace OpenMetricsPhp\Exposition\Text\Types;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Interfaces\ProvidesNamedValue;
use function addcslashes;
use function preg_match;
use function stripcslashes;

final class Label implements ProvidesNamedValue
{
	/** @var string */
	private $name;

	/** @var string */
	private $value;

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @throws InvalidArgumentException
	 */
	private function __construct( string $name, string $value )
	{
		$trimmedName  = trim( $name );
		$trimmedValue = trim( $value );

		$this->guardNameIsValid( $trimmedName );
		$this->guardValueIsValid( $trimmedValue );

		$this->name  = $trimmedName;
		$this->value = $trimmedValue;
	}

	/**
	 * @param string $name
	 * @param string $value
	 *
	 * @throws InvalidArgumentException
	 * @return Label
	 */
	public static function fromNameAndValue( string $name, string $value ) : self
	{
		return new self( $name, $value );
	}

	/**
	 * @param string $labelString
	 *
	 * @throws InvalidArgumentException
	 * @return Label
	 */
	public static function fromLabelString( string $labelString ) : self
	{
		$matches = [];
		if ( !preg_match( '#^([a-z_][a-z\d_]*)="(.+)"$#i', $labelString, $matches ) )
		{
			throw new InvalidArgumentException( 'Invalid label string.' );
		}

		return self::fromNameAndValue( $matches[1], stripcslashes( $matches[2] ) );
	}

	/**
	 * @param string $name
	 *
	 * @throws InvalidArgumentException
	 */
	private function guardNameIsValid( string $name ) : void
	{
		if ( !preg_match( '#^[a-z_][a-z\d_]*$#i', $name ) )
		{
			throw new InvalidArgumentException( 'Invalid label name.' );
		}
	}

	/**
	 * @param string $value
	 *
	 * @throws InvalidArgumentException
	 */
	private function guardValueIsValid( string $value ) : void
	{
		if ( '' === $value )
		{
			throw new InvalidArgumentException( 'Label value cannot be empty.' );
		}
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getValue() : string
	{
		return $this->value;
	}

	public function getLabelString() : string
	{
		return sprintf(
			'%s="%s"',
			$this->name,
			str_replace( "\n", '\n', addcslashes( $this->value, '"\\' ) )
		);
	}
}