<?php

namespace API;

interface EndpointResponseInterface
{
	public function getStatus(): int;

	public function getStatusText(): ?string;

	public function getHeaders(): ?array;

	public function getData(): ?array;
}