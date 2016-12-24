<?php

/**
 * Test: Api\Entity\PaymentFactory
 */

use Markette\GopayInline\Api\Entity\Payment;
use Markette\GopayInline\Api\Entity\PaymentFactory;
use Markette\GopayInline\Api\Lists\TargetType;
use Markette\GopayInline\Exception\ValidationException;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

// Required fields
test(function () {
	Assert::throws(function () {
		PaymentFactory::create(array());
	}, 'Markette\GopayInline\Exception\ValidationException', '%a%' . implode(', ', PaymentFactory::$required) . '%a%');
});

// Not allowed field
test(function () {
	$required = array(
		'amount' => 1,
		'currency' => 2,
		'order_number' => 3,
		'order_description' => 4,
		'items' => 5,
		'return_url' => 6,
		'notify_url' => 7,
	);
	$fields = array(
		'foo' => 8,
		'bar' => 9,
	);
	Assert::throws(function () use ($required, $fields) {
		PaymentFactory::create(array_merge($required, $fields));
	}, 'Markette\GopayInline\Exception\ValidationException', '%a%' . implode(', ', array_keys($fields)) . '%a%');
});

// Simple payment
test(function () {
	$data = array(
		'payer' => array(
			'default_payment_instrument' => 'BANK_ACCOUNT',
			'allowed_payment_instruments' => array('BANK_ACCOUNT'),
			'default_swift' => 'FIOBCZPP',
			'allowed_swifts' => array('FIOBCZPP', 'BREXCZPP'),
			'contact' => array(
				'first_name' => 'Zbynek',
				'last_name' => 'Zak',
				'email' => 'zbynek.zak@gopay.cz',
				'phone_number' => '+420777456123',
				'city' => 'C.Budejovice',
				'street' => 'Plana 67',
				'postal_code' => '373 01',
				'country_code' => 'CZE',
			),
		),
		'target' => array(
			'goid' => 123456,
			'type' => TargetType::ACCOUNT,
		),
		'amount' => 200,
		'currency' => 'CZK',
		'order_number' => '001',
		'order_description' => 'pojisteni01',
		'items' => array(
			array('name' => 'item01', 'amount' => 50, 'count' => 2),
			array('name' => 'item02', 'amount' => 100),
		),
		'additional_params' => array(
			array('name' => 'invoicenumber', 'value' => '2015001003'),
		),
		'return_url' => 'http://www.eshop.cz/return',
		'notify_url' => 'http://www.eshop.cz/notify',
		'lang' => 'cs',
	);

	$payment = PaymentFactory::create($data);
	Assert::type('Markette\GopayInline\Api\Entity\Payment', $payment);
});

// Validate order price and items price
test(function () {
	$data = array(
		'amount' => 200,
		'currency' => 2,
		'order_number' => 3,
		'order_description' => 4,
		'items' => array(
			array('name' => 'Item 01', 'amount' => 50, 'count' => 2),
			array('name' => 'Item 01', 'amount' => 50),
		),
		'return_url' => 6,
		'notify_url' => 7,
	);

	Assert::throws(function () use ($data) {
		PaymentFactory::create($data);
	}, 'Markette\GopayInline\Exception\ValidationException', '%a% (200) %a% (150) %a%');
});

// Validate items name
test(function () {
	$data = array(
		'amount' => 200,
		'currency' => 2,
		'order_number' => 3,
		'order_description' => 4,
		'items' => array(
			array('amount' => 50),
		),
		'return_url' => 6,
		'notify_url' => 7,
	);

	Assert::throws(function () use ($data) {
		PaymentFactory::create($data);
	}, 'Markette\GopayInline\Exception\ValidationException', "Item's name can't be empty or null.");
});

// Turn off validators
test(function () {
	// Invalid total price and items price
	$data = array(
		'amount' => 200,
		'currency' => 2,
		'order_number' => 3,
		'order_description' => 4,
		'items' => array(
			array('name' => 'Item 01', 'amount' => 50),
			array('name' => 'Item 02', 'amount' => 50),
		),
		'return_url' => 6,
		'notify_url' => 7,
	);

	try {
		PaymentFactory::create($data, array(PaymentFactory::V_PRICES => FALSE));
	} catch (Exception $e) {
		Assert::fail('Exception should not have been threw', $e, NULL);
	}

	// Invalid scheme
	$data = array(
		'amount' => 100,
		'currency' => 2,
		'order_number' => 3,
		'order_description' => 4,
		'items' => array(
			array('amount' => 50),
			array('amount' => 50),
		),
		'return_url' => 6,
		'notify_url' => 7,
		'x_unknown' => 1234,
		'y_foobar' => 5678,
	);

	try {
		PaymentFactory::create($data, array(PaymentFactory::V_SCHEME => FALSE));
	} catch (Exception $e) {
		Assert::fail('Exception should not have been threw', $e, NULL);
	}
});
