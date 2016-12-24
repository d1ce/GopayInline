<?php

/**
 * Test: Client
 */

use Markette\GopayInline\Api\Token;
use Markette\GopayInline\Auth\Auth;
use Markette\GopayInline\Client;
use Markette\GopayInline\Config;
use Markette\GopayInline\Exception\GopayException;
use Markette\GopayInline\Http\Http;
use Markette\GopayInline\Http\Request;
use Markette\GopayInline\Http\Response;
use Markette\GopayInline\Service\PaymentsService;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

// Default Auth/Http
test(function () {
	$config = new Config(1, 2, 3);
	$mock = Mockery::mock('Markette\GopayInline\Client', array($config))
		->makePartial()
		->shouldAllowMockingProtectedMethods();

	Assert::type('Markette\GopayInline\Http\Http', $mock->getHttp());
	Assert::type('Markette\GopayInline\Auth\Auth', $mock->getAuth());
});

// Token
test(function () {
	$config = new Config(1, 2, 3);
	$client = new Client($config);

	Assert::false($client->hasToken());
	Assert::null($client->getToken());

	$client->setToken($token = new Token);
	$token->accessToken = time();
	Assert::equal($token->accessToken, $client->getToken()->accessToken);

	$client->setToken($token = time() . time());
	Assert::equal($token, $client->getToken()->accessToken);
});

// Services
test(function () {
	$config = new Config(1, 2, 3);
	$client = new Client($config);

	Assert::type('Markette\GopayInline\Service\PaymentsService', $client->createPaymentsService());
	Assert::type('Markette\GopayInline\Service\PaymentsService', $client->payments);
	Assert::null($client->random);
});

// Call without token
test(function () {
	$config = new Config(1, 2, 3);
	$client = new Client($config);

	Assert::throws(function () use ($client) {
		$client->call(new Request());
	}, 'Markette\GopayInline\Exception\GopayException');
});

// Auth
test(function () {
	$config = new Config(1, 2, 3);
	$client = new Client($config);
	$token = 12345;

	$mock = Mockery::mock('Markette\GopayInline\Auth\Auth');
	$mock->shouldReceive('authenticate')->andReturnUsing(function () use ($token) {
		$r = new Response();
		$r->setData(array('access_token' => $token));

		return $r;
	});
	$client->setAuth($mock);
	$client->authenticate(array());

	Assert::equal($token, $client->getToken()->accessToken);
});

// Request
test(function () {
	$config = new Config(1, 2, 3);
	$client = new Client($config);
	$client->setToken(12345);
	$data = array('foo' => 'bar');

	$mock = Mockery::mock('Markette\GopayInline\Http\Http');
	$mock->shouldReceive('doRequest')->andReturnUsing(function () use ($data) {
		$r = new Response();
		$r->setData($data);

		return $r;
	});
	$client->setHttp($mock);
	$response = $client->call(new Request());

	Assert::type('Markette\GopayInline\Http\Response', $response);
	Assert::equal($data, $response->getData());
});
