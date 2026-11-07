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

<div>
	<div>
		<img src="<?php echo $Cap->GetImageEncoded() ?>" />
	</div>
	<div>
		<input type="hidden" name="captcha-token" value="<?php echo $Cap->GetTokenEncoded() ?>" />
		<input type="text" name="captcha-input" />
	</div>
</div>

```

```php

if(!Nether\Captcha::Validate($Token, $Input)) {
	echo 'failed to validate captcha.';
	return;
}

```