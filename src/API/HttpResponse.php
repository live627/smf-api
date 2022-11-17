<?php

namespace API;

class HttpResponse
{
	/**
	 * List of HTTP status codes
	 *
	 * From http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	 *
	 * @var array
	 */
	private array $status = [
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		207 => "Multi-Status",
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		307 => "Temporary Redirect",
		308 => "Permanent Redirect",
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
	 * @param ?array  $headers    List of additional headers
	 * @param ?array  $data       Data to output JSON-encoded
	 */
	public function __construct(
		int $status = 400,
		?string $statusText = null,
		?array $headers = null,
		?array $data = null
	)
	{
		if ($statusText === null && isset($this->status[$status]))
			$statusText = $this->status[$status];

		header(sprintf('HTTP/1.1 %d %s', $status, $statusText));

		if ($headers !== null)
			foreach ($headers as $key => $header)
			{
				if (!is_int($key))
					$header = $key . ': ' . $header;

				header($header);
			}

		if ($data !== null)
			echo json_encode($data);
	}
}
