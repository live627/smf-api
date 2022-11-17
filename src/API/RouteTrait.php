<?php

namespace API;

trait RouteTrait
{
	/** @var array */
	protected array $endpoints = [];

	/**
	 * @param string            $verb
	 * @param EndpointInterface $endpoint
	 */
	public function addEndpoint(string $verb, EndpointInterface $endpoint): void
	{
		$this->endpoints[$verb] = $endpoint;
	}

	/**
	 * @param string $verb
	 *
	 * @return bool
	 */
	public function hasEndpoint(string $verb): bool
	{
		return isset($this->endpoints[$verb]);
	}

	/**
	 * @param string $verb
	 *
	 * @return EndpointInterface
	 */
	public function getEndpoint(string $verb): EndpointInterface
	{
		return $this->endpoints[$verb];
	}
}