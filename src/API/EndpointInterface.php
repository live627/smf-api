<?php

namespace API;

interface EndpointInterface
{
	/**
	 * @return bool
	 */
	public function needsAuthentication(): bool;

	/**
	 * @return bool
	 */
	public function needsTheme(): bool;

	/**
	 * @return EndpointResponseInterface
	 */
	public function __invoke(): EndpointResponseInterface;
}
