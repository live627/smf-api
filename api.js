var encode = (payload, key) =>
{
	// Check key
	if (!key) {
		throw new Error('Require key');
	}

	var crypto = require('crypto');
	const base64urlEscape = str => str.replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
	const base64urlEncode = str => base64urlEscape(Buffer.from(str).toString('base64'));
	var header = { typ: 'JWT', alg: 'HS256' };
	var segments = [];
	segments.push(base64urlEncode(JSON.stringify(header)));
	segments.push(base64urlEncode(JSON.stringify(payload)));
	segments.push(base64urlEscape(crypto.createHmac('sha256', key).update(segments.join('.')).digest('base64')));

	return segments.join('.');
}, apiKey = encode({ id_member: 1 }, 'b8fef24b9ffae9cbf8f624de67909de5f63dcadd');

const performRequest = (host, path, method, dataObj) => new Promise((resolve, reject) =>
{
	var dataString = JSON.stringify(dataObj);
	var headers = { "Authorization": "Bearer " + apiKey };

	if (method !== 'GET')
		headers = {
			'Content-Type': 'application/json',
			'Content-Length': dataString.length
		};

	var options = { host, path, method, headers };
	const req = require('http').request(options, res =>
	{
		let data = '';
		res.on('data', chunk => data += chunk);
		res.on('end', () =>
		{
			res.data = data;
			resolve(res);
		});
	});
	req.on('error', reject);
	req.write(dataString);
	req.end();
});
performRequest('localhost', '/SMF2.1/api.php?endpoint=authenticated;u=1', 'GET', {}
).then(response =>
{
	const statusCode = response.statusCode;
	const colorNum = statusCode >= 400 ? 31 : (statusCode >= 300 ? 33 : 32);
	const char = statusCode >= 400 ? '✖' : (statusCode >= 300 ? '⚠' : '✔');
	console.log(
		'\u001B[%dm%s\u001B[39m Server responded with \u001B[1;%dm%s\u001B[22m %s\u001B[39m\n\##[group]Additional info\n%s\n##[endgroup]',
		colorNum,
		char,
		colorNum,
		statusCode,
		response.statusMessage,
		response.data
	);
	console.log(JSON.parse(response.data));
}).catch(err =>
{
	console.error(err);
});