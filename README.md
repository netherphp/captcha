# NetherPHP: Captcha ###########################################################

[![Packagist](https://img.shields.io/packagist/v/netherphp/captcha.svg?style=for-the-badge)](https://packagist.org/packages/netherphp/captcha)
[![Build Status](https://img.shields.io/github/actions/workflow/status/netherphp/captcha/phpunit.yml?style=for-the-badge)](https://github.com/netherphp/captcha/actions)
[![codecov](https://img.shields.io/codecov/c/gh/netherphp/captcha?style=for-the-badge&token=VQC48XNBS2)](https://codecov.io/gh/netherphp/captcha)

A locally produced Captcha.

## Features ####################################################################

* Generates captcha image.
* Configurable code length.
* Configurable time sensitivity.
* Simple API for validating token and codes.

## Requirements ################################################################

* PHP 8.5+
* PHP Imagick

## Install #####################################################################

`$ composer require netherphp/captcha`

## Usage #######################################################################

Get a new fully rendered captcha using the ::Fresh() factory method. Get a new
captcha where you can set settings before rendering with the ::New() factory
method.

```php
<?php

// prepare new captcha that is fully rendered.

$Cap = Nether\Captcha::Fresh();

// prepare captcha that can be configured before rendering.

$Cap = Nether\Captcha::New();
$Cap->SetTimeToExpire(60);
$Cap->Generate();

?>
```

Render the image in the form you want to protect with the captcha.

```html
<div>
	<div>
		<img src="<?php echo $Cap->GetImageEncoded() ?>" title="Captcha Image" />
	</div>
	<div>
		<p>Enter code from above image.</p>
		<input type="text" name="<?php echo $Cap->GetCodeFieldName() ?>" />
		<input type="hidden" name="<?php echo $Cap->GetTokenFieldName() ?>" value="<?php echo $Cap->GetTokenFieldValue() ?>" />
	</div>
</div>
```

Use the `::ValidateFromInput()` method to automatically find the captcha fields
from a dump of input with considerations handling the raw input automatically.

```php
$CaptchaOK = Nether\Captcha::ValidateFromInput($_POST);

if(!$CaptchaOK) {
	echo 'failed to validate captcha.';
	return;
}
```

Use the `::Validate()` method to manually validate known token and codes.

```php
$CaptchaOK = Nether\Captcha::Validate(
	$_POST['nether-captcha-token'],
	$_POST['nether-captcha-code']
);

if(!$CaptchaOK) {
	echo 'failed to validate captcha.';
	return;
}
```

## Todo ########################################################################

* Include front end templating.
