# NetherPHP: Captcha ###########################################################

[![Packagist](https://img.shields.io/packagist/v/netherphp/captcha.svg?style=for-the-badge)](https://packagist.org/packages/netherphp/captcha)
[![Build Status](https://img.shields.io/github/actions/workflow/status/netherphp/captcha/phpunit.yml?style=for-the-badge)](https://github.com/netherphp/captcha/actions)
[![codecov](https://img.shields.io/codecov/c/gh/netherphp/captcha?style=for-the-badge&token=VQC48XNBS2)](https://codecov.io/gh/netherphp/captcha)

A locally produced Captcha.

## Features ####################################################################

* Configurable code length.
* Configurable time sensitivity.
* Generates captcha image.
* API for validating token and codes.

## Todo ########################################################################

* Include front end templating.

## Usage Example ###############################################################

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

```html
<div>
	<div>
		<img src="<?php echo $Cap->GetImageEncoded() ?>" title="Captcha Image" />
	</div>
	<div>
		<p>Enter code from above image.</p>
		<input type="text" name="nether-captcha-input" />
		<input type="hidden" name="nether-captcha-token" value="<?php echo $Cap->GetTokenEncoded() ?>" />
	</div>
</div>

```

```php
// auto validate found captcha within supplied inputs.

$CaptchaOK = Nether\Captcha::ValidateFromInput($_POST);

if(!$CaptchaOK) {
	echo 'failed to validate captcha.';
	return;
}
```

```php
// manual validate known captcha data.

$CaptchaOK = Nether\Captcha::Validate(
	$_POST['nether-captcha-token'],
	$_POST['nether-captcha-code']
);

if(!$CaptchaOK) {
	echo 'failed to validate captcha.';
	return;
}
```