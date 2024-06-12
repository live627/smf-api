<?php

declare(strict_types=1);

spl_autoload_register(function ($class)
{
	$classMap = [
		'API\\' => 'API/',
	];
	call_integration_hook('integrate_autoload_api', [&$classMap]);

	foreach ($classMap as $prefix => $dirName)
	{
		$len = strlen($prefix);
		if (strncmp($prefix, $class, $len) !== 0)
			continue;

		$fileName = sprintf(
			'%s/%s%s.php',
			$GLOBALS['sourcedir'],
			$dirName,
			strtr(substr($class, $len), '\\', '/')
		);
		if (file_exists($fileName))
		{
			require_once $fileName;

			return;
		}
	}
});

require_once './SSI.php';
ob_end_clean();
add_integration_function('integrate_verify_user', 'API::FeignLoginIntegration', false);

class API
{
	private static int $memID;

	public static function FeignLoginIntegration(): int
	{
		return self::$memID;
	}

	/**
	 * @param int $memID member to log in
	 */
	public static function FeignLogin(int $memID = 1): void
	{
		self::$memID = $memID;
		loadUserSettings();
		loadPermissions();
	}

	public static function getUserSecretToken(int $memID): ?string
	{
		global $smcFunc;

		$request = $smcFunc['db_query'](
			'', '
			SELECT token
			FROM {db_prefix}api_tokens
			WHERE id_member = {int:memID}',
			[
				'memID' => $memID,
			]
		);
		[$token] = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return bin2hex($token);
	}

	public static function setUserSecretToken(int $memID): string
	{
		global $smcFunc;

		$found = '0';

		do
		{
			$token = bin2hex(random_bytes(20));

			// Chances are low, but we need to check nonetheless, that no one else has this token
			$request = $smcFunc['db_query'](
				'', '
				SELECT 1
				FROM {db_prefix}api_tokens
				WHERE token = {string:token}',
				[
					'token' => $token,
				]
			);
			[$found] = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);
		}
		while ($found === '0');

		$smcFunc['db_insert'](
			'insert',
			'{db_prefix}api_tokens',
			['id_member' => 'int', 'token' => 'raw'],
			[$memID, 'X\'' . $token . '\''],
			['id_member']
		);

		return $token;
	}
}

try
{

	$versions = ['v1'];
	$version = $_GET['version'] ?? 'v1';

	if (!in_array($version, $versions))
		throw new API\HttpException(400, 'Unknown version');

	$method = $_SERVER['REQUEST_METHOD'];
	if ($method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
		if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
			$method = 'DELETE';
		elseif ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
			$method = 'PUT';
		else
			throw new API\HttpException(400, 'Unexpected Header');

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Content-Type: application/json');
	header('Accept: application/jsonm;application/x-www-form-urlencoded;multipart/form-data');

	if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json')
	{
		$data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

		if (isset($data['data']) && is_array($data['data']) && !isset($data['data'][0]))
			$data = array_merge($data, $data['data']);

		foreach ($data as $key => $value)
		{
			$_POST[$key] = $value;
			$_REQUEST[$key] = $value;
		}
	}

	$route = sprintf(
		'API\Routes\%s',
		preg_replace_callback(
			'/-.?/',
			fn($m) => strtoupper($m[0][1] ?? ''),
			'-' . strtolower($_GET['endpoint'] ?? '')
		)
	);
	if (!class_exists($route))
		throw new API\HttpException(400, 'No Route Specified');

	$obj = new $route;
	if (!$obj instanceof Api\RouteInterface)
		throw new API\HttpException(400);

	if ($obj->hasEndpoint($method))
	{
		$endpoint = $obj->getEndpoint($method);
		if ($endpoint->needsAuthentication())
		{
			$tok = API\JWT::get_bearer_token();
			$payload = API\JWT::decode($tok);
			$secret = API::getUserSecretToken($payload->id_member);
			if (!API\JWT::is_valid($tok, $secret))
				throw new API\HttpException(401);

			API::FeignLogin($payload->id_member);

			if ($endpoint->needsTheme())
			{
				$GLOBALS['txt']['time_format'] = '';
				$GLOBALS['settings']['theme_id'] = 0;
				loadTheme();
			}
		}
		$resp = $endpoint();
		new API\HttpResponse($resp->getStatus(), $resp->getStatusText(), $resp->getHeaders(), $resp->getData());
	}
	else
		throw new API\HttpException(405);
}
catch (JsonException $exception)
{
	new API\HttpResponse(400, $exception->getMessage());
}
catch (API\HttpException $exception)
{
	new API\HttpResponse($exception->getCode(), $exception->getMessage());
}