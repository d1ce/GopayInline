<?php

/**
 * Test: Auth\Oauth2Client
 */

use Markette\GopayInline\Auth\Oauth2Client;
use Markette\GopayInline\Client;
use Markette\GopayInline\Exception\AuthorizationException;
use Markette\GopayInline\Http\HttpClient;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

// Simple
test(function () {
	$client = Mockery::namedMock('Client1', 'Markette\GopayInline\Client');
	$client->shouldReceive('getClientId')->andReturn(1);
	$client->shouldReceive('getClientSecret')->andReturn(2);

	$response = Mockery::mock();
	$response->shouldReceive('getData')->andReturn(array('foo' => 'bar'));

	$http = Mockery::mock('Markette\GopayInline\Http\HttpClient');
	$http->shouldReceive('doRequest')->andReturn($response);

	$oauth2 = new Oauth2Client($client, $http);
	$response2 = $oauth2->authenticate(array('scope' => 'foobar'));

	Assert::same($response, $response2);
});

// cURL error
test(function () {
	$client = Mockery::namedMock('Client2', 'Markette\GopayInline\Client');
	$client->shouldReceive('getClientId')->andReturn(1);
	$client->shouldReceive('getClientSecret')->andReturn(2);

	$response = Mockery::mock();
	$response->shouldReceive('getData')->andReturn(FALSE);
	$response->shouldReceive('getCode')->andReturn(404);

	$http = Mockery::mock('Markette\GopayInline\Http\HttpClient');
	$http->shouldReceive('doRequest')->andReturn($response);

	$oauth2 = new Oauth2Client($client, $http);

	Assert::exception(function () use ($oauth2) {
		$oauth2->authenticate(array('scope' => 'foobar'));
	}, 'Markette\GopayInline\Exception\AuthorizationException');
});

// Gopay error
test(function () {
	$client = Mockery::namedMock('Client3', 'Markette\GopayInline\Client');
	$client->shouldReceive('getClientId')->andReturn(1);
	$client->shouldReceive('getClientSecret')->andReturn(2);

	$response = Mockery::mock();
	$error = (object) array(
		'errors' => array(
			0 => (object) array(
				'error_code' => 500,
				'scope' => 'G',
				'field' => 'foobar',
				'message' => 'foo foo foo',
			),
		),
	);
	$response->shouldReceive('getData')->andReturn($error);

	$http = Mockery::mock('Markette\GopayInline\Http\HttpClient');
	$http->shouldReceive('doRequest')->andReturn($response);

	$oauth2 = new Oauth2Client($client, $http);

	Assert::exception(function () use ($oauth2) {
		$oauth2->authenticate(array('scope' => 'foobar'));
	}, 'Markette\GopayInline\Exception\AuthorizationException', '#500 (G) [foobar] foo foo foo');
});
