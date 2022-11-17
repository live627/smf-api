<?php

namespace API;

trait EndpointResponseTrait
{
	private int $status;
	private ?string $statusText;
	private ?array $headers;
	private ?array $data;

	/**
	 * @param array|null  $data
	 * @param int         $status
	 * @param string|null $statusText
	 * @param array|null  $headers
	 */
	public function __construct(?array $data, int $status = 500, ?string $statusText = null, ?array $headers = null)
	{
		$this->data = $data;
		$this->status = $status;
		$this->statusText = $statusText;
		$this->headers = $headers;
	}

	/**
	 * @return array
	 */
	public function getData(): ?array
	{
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->status;
	}

	/**
	 * @return string
	 */
	public function getStatusText(): ?string
	{
		return $this->statusText;
	}

	/**
	 * @return array
	 */
	public function getHeaders(): ?array
	{
		return $this->headers;
	}
}