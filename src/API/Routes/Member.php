<?php

namespace API\Routes;

use API\{Endpoint, RouteInterface, RouteTrait};

class Member implements RouteInterface
{
	use RouteTrait;

	public function __construct()
	{
		$this->addEndpoint(RouteInterface::HTTP_VERB_GET, new Endpoint\Member\Get);
	}
}
