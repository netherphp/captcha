<?php ##########################################################################
################################################################################

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Firebase\JWT;

use Nether\Captcha;

################################################################################
################################################################################

class CaptchaTest
extends TestCase {

	#[Test]
	public function
	CheckMisbehaviourFirebaseJWT():
	void {

		// i do not have a reason for doing this other than wanting to be
		// sure that my third party isn't being a pain in the ass. firebase
		// jwt has historically been annoying at times i wasn't even 
		// directly using it like in this project.

		$Hash = hash('sha512', 'test');
		$Algo = 'HS512';
		$Now = time();

		// nothing should be wrong.

		$Payload = new Captcha\Payload('Test Payload', $Now, ($Now + 30));
		$Token = JWT\JWT::encode($Payload->ToPayload(), $Hash, $Algo);
		$Err = NULL;

		try { JWT\JWT::decode($Token, new JWT\Key($Hash, $Algo)); }
		catch(Exception $Err) { }

		$this->assertNull($Err);

		// the token should not validate because incorrect key.

		$Payload = new Captcha\Payload('Test Payload', $Now, ($Now + 30));
		$Token = JWT\JWT::encode($Payload->ToPayload(), $Hash, $Algo);
		$Err = NULL;

		try { JWT\JWT::decode($Token, new JWT\Key(strrev($Hash), $Algo)); }
		catch(Exception $Err) { }

		$this->assertInstanceOf(JWT\SignatureInvalidException::class, $Err);

		// the token should not validate because expired.

		$Payload = new Captcha\Payload('Test Payload', $Now, ($Now - 30));
		$Token = JWT\JWT::encode($Payload->ToPayload(), $Hash, $Algo);
		$Err = NULL;

		try { JWT\JWT::decode($Token, new JWT\Key($Hash, $Algo)); }
		catch(Exception $Err) { }

		$this->assertInstanceOf(JWT\ExpiredException::class, $Err);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Test]
	public function
	FromConstructor():
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
	FromNewFactory():
	void {

		$Cap = Captcha::New();
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
	FromFreshFactory():
	void {

		$Cap = Captcha::Fresh();
		
		$this->assertSame(
			$Cap->Config->KeyLength,
			strlen($Cap->GetKey())
		);

		$this->assertTrue(Captcha::Validate(
			$Cap->GetToken(), $Cap->GetKey()
		));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Test]
	public function
	FailBeacuseIncorrectKey():
	void {

		$Cap = Captcha::New();
		$Cap->Generate();

		$this->assertFalse(Captcha::Validate(
			$Cap->GetToken(),
			'chumbawamba'
		));

		return;
	}

	#[Test]
	public function
	FailBeacuseExpired():
	void {

		$Cap = Captcha::New();
		$Cap->SetTimeToExpire(-30);
		$Cap->Generate();

		$this->assertFalse(Captcha::Validate(
			$Cap->GetToken(),
			$Cap->GetKey()
		));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
