<?php

namespace API\Endpoint\Member;

use API\{EndpointInterface, EndpointResponseInterface, HttpException, Response};

class Get implements EndpointInterface
{
	/**
	 * @inheritDoc
	 */
	public function needsAuthentication(): bool
	{
		return !isset($_GET['u']);
	}

	/**
	 * @inheritDoc
	 */
	public function needsTheme(): bool
	{
		return false;
	}

	/**
	 * @inheritDoc
	 * @throws HttpException
	 */
	public function __invoke(): EndpointResponseInterface
	{
		global $smcFunc, $user_settings, $user_info;

		if (!isset($_GET['u']))
		{
			if ($user_info['id'] == 0)
				throw new HttpException(401);

			return new Response(
				[
					'username' => $user_info['username'],
					'name' => $user_info['name'],
					'id' => (int) $user_info['id'],
					'posts' => (int) $user_info['posts'],
					'registered_at' => (int) ($user_settings['date_registered'] ?? 0),
				],
				200
			);
		}
		elseif ($_GET['u'] == 0 && !ctype_digit($_GET['u']))
			throw new HttpException(400);

		$request = $smcFunc['db_query'](
			'', '
			SELECT id_member, member_name, real_name, date_registered, posts
			FROM {db_prefix}members
			WHERE id_member = {int:id_member}',
			[
				'id_member' => $_GET['u'],
			]
		);

		if ($smcFunc['db_num_rows']($request) == 0)
			throw new HttpException(404);

		$profile = $smcFunc['db_fetch_assoc']($request);
		$smcFunc['db_free_result']($request);

		return new Response(
			[
				'username' => $profile['member_name'],
				'name' => $profile['real_name'],
				'id' => (int) $profile['id_member'],
				'posts' => (int) $profile['posts'],
				'registered_at' => (int) ($profile['date_registered'] ?? 0),
			],
			200
		);
	}
}
