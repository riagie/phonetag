<?php

require __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Protocols\Http\Request;
use Workerman\Connection\TcpConnection;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->load(__DIR__ . '/.env');

$worker            = new Worker("http://{$_ENV['HOST']}:{$_ENV['PORT']}");
$worker->count     = 4;
$worker->onMessage = function (TcpConnection $connection, Request $request) {
	try {
		if ($request->header('Authorization') !== 'Bearer ' . $_ENV['API_KEY']
			|| $request->method() !== 'POST'
			|| $request->path() !== '/tags') {
			throw new Exception('invalid request parameter');
		}

		$data  = json_decode($request->rawBody(), true);
		$phone = preg_replace('/\D/', '', $data['number'] ?? '');
		$phone = match (true) {
			str_starts_with($phone, '62') => $phone,
			str_starts_with($phone, '0')  => '62' . substr($phone, 1),
			str_starts_with($phone, '8')  => '62' . $phone,
			default                       => '+' . $phone
		};

		$httpClient = HttpClient::create();
		$response   = $httpClient->request('POST', $_ENV['GET_CONTACT_BASE_URL'] . '/list-tag', [
			'headers' => [
				'Accept'             => 'application/json, text/javascript, */*; q=0.01',
				'Accept-Language'    => 'en-US,en;q=0.9,id;q=0.8',
				'Content-Type'       => 'application/x-www-form-urlencoded; charset=UTF-8',
				'Cookie'             => "accessToken={$_ENV['GET_CONTACT_TOKEN']}; lang=en;",
				'Origin'             => $_ENV['GET_CONTACT_BASE_URL'],
				'Referer'            => $_ENV['GET_CONTACT_BASE_URL'] . '/search',
				'Sec-CH-UA'          => '"Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
				'Sec-CH-UA-Mobile'   => '?0',
				'Sec-CH-UA-Platform' => '"Windows"',
				'Sec-Fetch-Dest'     => 'empty',
				'Sec-Fetch-Mode'     => 'cors',
				'Sec-Fetch-Site'     => 'same-origin',
				'X-Requested-With'   => 'XMLHttpRequest',
			],
			'body' => http_build_query([
				'hash'        => $_ENV['GET_CONTACT_KEY'],
				'phoneNumber' => $phone,
				'countryCode' => 'ID',
			]),
		]);

		$statusCode      = $response->getStatusCode();
		$responseContent = $response->toArray();
		if ($response->getStatusCode() !== 200 ||
			($responseContent['status'] ?? '') === 'error' ||
			!isset($responseContent['tags']) ||
			count($responseContent['tags']) < 2) {
			$connection->send(json_encode([
				'message' => count($responseContent['tags'] ?? []) < 2 ? 'no result found' : strtolower($responseContent['message']),
			]));
			return;
		}

		$connection->send(json_encode([
			'data' => $responseContent['tags'],
		]));
	} catch (\Exception $e) {
		$connection->send(json_encode([
			'message' => $e->getMessage(),
		]));
	}
};

Worker::runAll();
