<?php

namespace API;

interface RouteInterface
{
	/** @var string Fetch a resource */
	public const HTTP_VERB_GET = 'GET';

	/** @var string Fetch a resource */
	public const HTTP_VERB_POST = 'POST';

	/**
	 * Replace an entire resource. Should throw an error if some required
	 * parameters are missing, similar to object creation.
	 *
	 * @var string
	 */
	public const HTTP_VERB_PUT = 'PUT';

	/** @var string Update a resource */
	public const HTTP_VERB_PATCH = 'PATCH';

	/** @var string Delete a resource */
	public const HTTP_VERB_DELETE = 'DELETE';

	/**
	 * @param string            $verb
	 * @param EndpointInterface $endpoint
	 */
	public function addEndpoint(string $verb, EndpointInterface $endpoint): void;

	/**
	 * @param string $verb
	 *
	 * @return bool
	 */
	public function hasEndpoint(string $verb): bool;

	/**
	 * @param string $verb
	 *
	 * @return EndpointInterface
	 */
	public function getEndpoint(string $verb): EndpointInterface;
}