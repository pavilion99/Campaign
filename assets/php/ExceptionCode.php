<?php
/**
 * Created by PhpStorm.
 * User: me
 * Date: 2018-02-14
 * Time: 08:32
 */

abstract class ExceptionCode {
	public const MALFORMED_REQUEST = 0x1000;
	public const UNAUTHENTICATED = 0x1001;
	public const ARITY_MISMATCH = 0x1002;

	public static function text(int $code): ?string {
		$text = null;

		switch ($code) {
			case self::MALFORMED_REQUEST: {
				$text = "The request was improperly formed. Make sure you included both a username and API key in your request.";
				break;
			}
			case self::UNAUTHENTICATED: {
				$text = "Incorrect username or API key. Check your credentials and try again.";
				break;
			}
			case self::ARITY_MISMATCH: {
				$text = "Argument count mismatch. Check the API documentation for the correct number of arguments to use in this request.";
				break;
			}
		}

		return $text;
	}

	/** @throws Exception */
	public static function throw(int $code) {
		throw new Exception(self::text($code), $code);
	}
}