<?php

require('vendor/autoload.php');

$Cap = new Nether\Captcha();
$Cap->Generate();

var_dump($Cap);

var_dump(Nether\Captcha::Validate(
	$Cap->GetToken(),
	$Cap->GetKey()
));
