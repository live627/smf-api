<?php

declare(strict_types=1);

namespace API;

use stdClass;

class JWT
{
	private static int $leeway = 30;
	private static int $timestamp = 0;

	/**
	 * @param array  $headers
	 * @param array  $payload
	 * @param string $secret
	 *
	 * @return string
	 */
	public static function generate(array $headers, array $payload, string $secret = 'secret'): string
	{
		$headers_encoded = self::base64_url_encode(json_encode($headers));
		$payload_encoded = self::base64_url_encode(json_encode($payload));

		return sprintf(
			'%s.%s.%s',
			$headers_encoded,
			$payload_encoded,
			self::base64_url_encode(
				hash_hmac(
					'SHA256',
					$headers_encoded . '.' . $payload_encoded,
					$secret,
					true
				)
			)
		);
	}

	/**
	 * @param string $jwt
	 *
	 * @return stdClass
	 * @throws HttpException
	 */
	public static function decode(string $jwt): stdClass
	{
		$tks = explode('.', $jwt);
		if (count($tks) !== 3)
			throw new HttpException(401, 'Wrong number of segments');
		if (($header = json_decode(self::base64_url_decode($tks[0]))) === null)
			throw new HttpException(401, 'Invalid header encoding');
		if (!isset($header->alg))
			throw new HttpException(401, 'Empty algorithm');
		if ($header->alg != 'HS256')
			throw new HttpException(401, 'Algorithm not supported');

		if (($payload = json_decode(self::base64_url_decode($tks[1]))) === null)
			throw new HttpException(401, 'Invalid claims encoding');

		$timestamp = self::$timestamp === 0 ? time() : self::$timestamp;
		// Check the nbf if it is defined. This is the time that the
		// token can actually be used. If it's not yet that time, abort.
		if (isset($payload->nbf) && $payload->nbf > ($timestamp + self::$leeway))
			throw new HttpException(
				401,
				sprintf('Cannot handle token prior to %s', date('c', $payload->nbf))
			);

		// Check that this token has been created before 'now'. This prevents
		// using tokens that have been created for later use (and haven't
		// correctly used the nbf claim).
		if (isset($payload->iat) && $payload->iat > ($timestamp + self::$leeway))
			throw new HttpException(
				401,
				sprintf('Cannot handle token prior to %s', date('c', $payload->iat))
			);

		// Check if this token has expired.
		if (isset($payload->exp) && ($timestamp - self::$leeway) >= $payload->exp)
			throw new HttpException(401, 'Expired token');

		return $payload;
	}

	/**
	 * @param string $jwt
	 * @param string $secret
	 *
	 * @return bool
	 */
	public static function is_valid(string $jwt, string $secret = 'secret'): bool
	{
		$tks = explode('.', $jwt);
		$signed = self::base64_url_encode(
			hash_hmac(
				'SHA256',
				$tks[0] . '.' . $tks[1],
				$secret,
				true
			)
		);

		return hash_equals($signed, $tks[2]);
	}

	private static function base64_url_encode($data): string
	{
		// Base64 alphabet is [A-Za-z0-9+/], padded by [=].
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	private static function base64_url_decode($data): string
	{
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '='));
	}

	/**
	 * @return string
	 * @throws HttpException
	 */
	public static function get_bearer_token(): string
	{
		$header = trim($_SERVER['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '');
		if ($header == '' && function_exists('apache_request_headers'))
		{
			$headers = apache_request_headers();
			/* Server-side fix for bug in old Android versions (a nice
			 * side effect of this fix means we don't care about
			 * capitalization of the Authorization) header.
			 */
			$headers = array_combine(
				array_map(
					'ucwords',
					array_keys($headers)
				),
				array_values($headers)
			);
			if (isset($headers['Authorization']))
				$header = trim($headers['Authorization']);
		}

		// HEADER: Get the access token from the header
		if ($header != '' && preg_match('/Bearer\s(\S+)/', $header, $matches))
			return $matches[1];

		throw new HttpException(400, 'Invalid authorization header');
	}
}
