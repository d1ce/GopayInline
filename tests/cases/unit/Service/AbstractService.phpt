<?php

/**
 * Test: Service\AbstractService
 */

namespace Tests\Cases\Unit\Service;

use Markette\GopayInline\Client;
use Markette\GopayInline\Exception\InvalidStateException;
use Markette\GopayInline\Http\Http;
use Markette\GopayInline\Http\Request;
use Markette\GopayInline\Http\Response;
use Markette\GopayInline\Service\AbstractService;
use Mockery;
use RuntimeException;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class DummyService extends AbstractService
{

	/**
	 * @param string $method
	 * @param string $uri
	 * @param array|NULL $data
	 * @param string $contentType
	 * @return Response
	 */
	public function makeRequest($method, $uri, array $data = NULL, $contentType = Http::CONTENT_JSON)
	{
		return parent::makeRequest($method, $uri, $data, $contentType);
	}

}

// No token
test(function () {
	$client = Mockery::namedMock('Client1', 'Markette\GopayInline\Client');
	$client->shouldReceive('hasToken')->andReturn(FALSE);
	$client->shouldReceive('authenticate')->andThrow('RuntimeException');

	$service = Mockery::mock('Tests\Cases\Unit\Service\DummyService', array($client))->makePartial();
	$service->shouldAllowMockingProtectedMethods();

	Assert::throws(function () use ($service) {
		$service->makeRequest('GET', 'test');
	}, 'RuntimeException');
});

// Simple get
test(function () {
	$client = Mockery::namedMock('Client2', 'Markette\GopayInline\Client');
	$client->shouldReceive('hasToken')->andReturn(FALSE);
	$client->shouldReceive('authenticate');
	$client->shouldReceive('getToken')->andReturn((object) array('accessToken' => 12345));
	$client->shouldReceive('call')->andReturnUsing(function (Request $request) {
		return $request;
	});

	$service = Mockery::mock('Tests\Cases\Unit\Service\DummyService', array($client));

	/** @var Request $request */
	$request = $service->makeRequest('GET', 'foobar');
	Assert::match('%a%foobar', $request->getUrl());
	Assert::true(in_array(CURLOPT_HTTPGET, $request->getOpts()));
});

// Simple post
test(function () {
	$client = Mockery::namedMock('Client3', 'Markette\GopayInline\Client');
	$client->shouldReceive('hasToken')->andReturn(TRUE);
	$client->shouldReceive('getToken')->andReturn((object) array('accessToken' => 12345));
	$client->shouldReceive('call')->andReturnUsing(function (Request $request) {
		return $request;
	});

	$service = Mockery::mock('Tests\Cases\Unit\Service\DummyService', array($client));
	$data = array('foo' => 1, 'bar' => 2);

	/** @var Request $request */
	$request = $service->makeRequest('POST', 'foobar', $data);
	Assert::match('%a%foobar', $request->getUrl());
	Assert::true(in_array(CURLOPT_POST, $request->getOpts()));
	$opts = $request->getOpts();
	Assert::same($data, json_decode($opts[CURLOPT_POSTFIELDS], TRUE));
});

// Invalid method
test(function () {
	$client = Mockery::namedMock('Client3', 'Markette\GopayInline\Client');
	$client->shouldReceive('hasToken')->andReturn(TRUE);
	$client->shouldReceive('getToken')->andReturn((object) array('accessToken' => 12345));
	$client->shouldReceive('call')->andReturnUsing(function (Request $request) {
		return $request;
	});

	$service = Mockery::mock('Tests\Cases\Unit\Service\DummyService', array($client));

	Assert::throws(function () use ($service) {
		$service->makeRequest('FUCK', 'foobar');
	}, 'Markette\GopayInline\Exception\InvalidStateException');
});
