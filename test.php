<?php

require('vendor/autoload.php');

$Cap = Nether\Captcha::Fresh();
$Img = $Cap->NewImage();
$Img->Render();

var_dump($Img);