<?php

require('vendor/autoload.php');

$Cap = Nether\Captcha::Fresh();

file_put_contents('test.png', $Cap->GetImageBinary());