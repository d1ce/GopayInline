<?php

/**
 * Test: Api\Token
 */

use Markette\GopayInline\Api\Token;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

// Simple token
test(function () {
	$token = Token::create($data = array(
		'access_token' => 1,
		'refresh_token' => 2,
		'token_type' => 3,
		'expires_in' => 4,
	));

	Assert::equal($data['access_token'], $token->accessToken);
	Assert::equal($data['refresh_token'], $token->refreshToken);
	Assert::equal($data['token_type'], $token->type);
	Assert::equal($data['expires_in'], $token->expireIn);
});
