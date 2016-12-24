<?php

/**
 * Test: Http\HttpClient
 */

use Markette\GopayInline\Exception\HttpException;
use Markette\GopayInline\Http\HttpClient;
use Markette\GopayInline\Http\Io;
use Markette\GopayInline\Http\Request;
use Markette\GopayInline\Http\Response;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

// FALSE response
test(function () {
	$io = Mockery::mock('Markette\GopayInline\Http\Io');
	$io->shouldReceive('call')->andReturn(FALSE);
	$http = new HttpClient();
	$http->setIo($io);

	Assert::throws(function () use ($http) {
		$http->doRequest(new Request());
	}, 'Markette\GopayInline\Exception\HttpException');
});

// Error response
test(function () {
	$error = (object) array('error_code' => 500, 'scope' => 'S', 'field' => 'F', 'message' => 'M');
	$io = Mockery::mock('Markette\GopayInline\Http\Io');
	$io->shouldReceive('call')->andReturnUsing(function () use ($error) {
		$r = new Response();
		$r->setData(array('errors' => array($error)));

		return $r;
	});
	$http = new HttpClient();
	$http->setIo($io);

	Assert::throws(function () use ($http, $error) {
		$http->doRequest(new Request());
	}, 'Markette\GopayInline\Exception\HttpException', HttpException::format($error));
});

// Success response
test(function () {
	$data = array('a' => 'b');
	$io = Mockery::mock('Markette\GopayInline\Http\Io');
	$io->shouldReceive('call')->andReturnUsing(function () use ($data) {
		$r = new Response();
		$r->setData($data);

		return $r;
	});
	$http = new HttpClient();
	$http->setIo($io);

	Assert::same($data, $http->doRequest(new Request())->data);
});
