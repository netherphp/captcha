# NetherPHP: Captcha ###########################################################

[![Packagist](https://img.shields.io/packagist/v/netherphp/captcha.svg?style=for-the-badge)](https://packagist.org/packages/netherphp/captcha)
[![Build Status](https://img.shields.io/github/actions/workflow/status/netherphp/captcha/phpunit.yml?style=for-the-badge)](https://github.com/netherphp/captcha/actions)
[![codecov](https://img.shields.io/codecov/c/gh/netherphp/captcha?style=for-the-badge&token=VQC48XNBS2)](https://codecov.io/gh/netherphp/captcha)

A locally produced Captcha.

## Documentation ###############################################################

```php
<?php

$Cap = Nether\Captcha::Fresh();

?>
```

```html
<div>
	<div>
		<img src="<?php echo $Cap->GetImageEncoded() ?>" />
	</div>
	<div>
		<p>Enter code from above image.</p>
		<input type="text" name="captcha-input" />
		<input type="hidden" name="captcha-token" value="<?php echo $Cap->GetTokenEncoded() ?>" />
	</div>
</div>

```

```php
$CaptchaOK = Nether\Captcha::Validate(
	$_POST['captcha-token'],
	$_POST['captcha-input']
);

if(!$CaptchaOK) {
	echo 'failed to validate captcha.';
	return;
}

```