<?php

namespace API;

use Exception;

class HttpException extends Exception
{
	/**
	 * List of HTTP status codes
	 *
	 * From http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	 *
	 * @var array
	 */
	private array $status = [
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot', // RFC 2324
		426 => 'Upgrade Required', // RFC 2817
		428 => 'Precondition Required', // RFC 6585
	];

	/**
	 * @param int     $status     Defaults to 400
	 * @param ?string $statusText If null, will use the default status phrase
	 */
	public function __construct(int $status = 400, string $statusText = null)
	{
		if ($statusText === null && isset($this->status[$status]))
			$statusText = $this->status[$status];
		parent::__construct($statusText, $status);
	}
}