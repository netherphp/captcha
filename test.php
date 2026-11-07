<?php

require('vendor/autoload.php');

$Cap = Nether\Captcha::Fresh();

var_dump($Cap->GetImageEncoded());
