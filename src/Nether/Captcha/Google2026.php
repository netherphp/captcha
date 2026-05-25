<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

use Nether\Atlantis;
use Nether\Common;
use Nether\Surface;

################################################################################
################################################################################

#[Common\Meta\Info('Uses the "reCaptcha Enterprise" API Google is pushing in 2026.')]
class Google2026 {

	const
	ConfKeyID = 'Nether.Captcha.Google2026.KeyID';

	public string
	$SiteID = '';

	public string
	$DefaultHTML = '';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $SiteID) {
		$this->SiteID = $SiteID;
		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetRemoteScriptURL():
	string {

		$Output = sprintf(
			'https://www.google.com/recaptcha/enterprise.js?render=%s',
			$this->SiteID
		);

		return $Output;
	}

	public function
	GetLocalScriptURL():
	string {

		$Output = sprintf(
			'/share/atlantis/js/interfaces/gre2026.js?render=%s',
			$this->SiteID
		);

		return $Output;
	}

};
