<?php

/**
 * Test: Http\Request
 */

use Markette\GopayInline\Http\Request;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

// Empty request
test(function () {
	$r = new Request();

	Assert::null($r->getUrl());
	Assert::equal(array(), $r->getData());
	Assert::equal(array(), $r->getHeaders());
	Assert::equal(array(), $r->getOpts());
});
// Simple request
test(function () {
	$r = new Request();
	$r->setData($data = array('foo' => 'bar'));
	$r->setHeaders($headers = array('foo1' => 'bar1'));
	$r->setOpts($opts = array('foo2' => 'bar2'));
	$r->setUrl($url = 'www.foo.bar');

	Assert::equal($data, $r->getData());
	Assert::equal($headers, $r->getHeaders());
	Assert::equal($opts, $r->getOpts());
	Assert::equal($url, $r->getUrl());
});

// Append headers/opts
test(function () {
	$r = new Request();

	Assert::equal(array(), $r->getHeaders());
	Assert::equal(array(), $r->getOpts());

	$r->appendHeaders(array('h' => 1));
	$r->appendOpts(array('o' => 1));
	Assert::equal(array('h' => 1), $r->getHeaders());
	Assert::equal(array('o' => 1), $r->getOpts());

	$r->appendHeaders(array('h2' => 2));
	$r->appendOpts(array('o2' => 2));
	Assert::equal(array('h' => 1, 'h2' => 2), $r->getHeaders());
	Assert::equal(array('o' => 1, 'o2' => 2), $r->getOpts());

	$r->addHeader('h3', 3);
	$r->addOpt('o3', 3);
	Assert::equal(array('h' => 1, 'h2' => 2, 'h3' => 3), $r->getHeaders());
	Assert::equal(array('o' => 1, 'o2' => 2, 'o3' => 3), $r->getOpts());
});
