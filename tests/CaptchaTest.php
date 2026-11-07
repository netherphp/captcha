<?php ##########################################################################
################################################################################

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use Nether\Captcha;

################################################################################
################################################################################

class CaptchaTest
extends TestCase {

	#[Test]
	public function
	BasicTest():
	void {

		$Cap = new Captcha;
		$Cap->SetKey('banana');
		$Cap->Generate();

		// $Cap->GetToken() is shipped to the form and the user is to
		// return it to us plus the password they think unlocks it likely
		// read from an image rendering of gibberish letters.

		$this->assertFalse(Captcha::Validate($Cap->GetToken(), 'orange'));
		$this->assertTrue(Captcha::Validate($Cap->GetToken(), 'banana'));

		return;
	}

	#[Test]
	public function
	SetKeyNullResumesRandom():
	void {

		$Cap = new Captcha;

		$Cap->SetKey('banana');
		$Cap->Generate();
		$this->assertEquals('banana', $Cap->GetKey());
		$this->assertTrue(Captcha::Validate($Cap->GetToken(), 'banana'));

		$Cap->SetKey(NULL);
		$Cap->Generate();
		$this->assertNotEquals('banana', $Cap->GetKey());
		$this->assertGreaterThan(1, strlen($Cap->GetKey()));
		$this->assertFalse(Captcha::Validate($Cap->GetToken(), 'banana'));
		$this->assertTrue(Captcha::Validate($Cap->GetToken(), $Cap->GetKey()));

		return;
	}

};
