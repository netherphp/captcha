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
		catch(Exception $Err) { } // -------------^^^^^^

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
	MethodSetAlgoHash():
	void {

		$Err = NULL;
		$Cap = new Captcha;

		////////

		$Cap->SetAlgoHash('sha256');
		$this->assertEquals('sha256', $Cap->GetAlgoHash());

		$Cap->SetAlgoHash('md5');
		$this->assertEquals('md5', $Cap->GetAlgoHash());

		////////

		try { $Cap->SetAlgoHash('qwertyasdf'); }
		catch(Exception $Err) { }

		$this->assertInstanceOf(Captcha\Error\AlgoUnsupported::class, $Err);

		/////////

		return;
	}

	#[Test]
	public function
	MethodSetAlgoToken():
	void {

		$Err = NULL;
		$Cap = new Captcha;

		////////

		$Cap->SetAlgoToken('ES256');
		$this->assertEquals('ES256', $Cap->GetAlgoToken());

		$Cap->SetAlgoToken('ES384');
		$this->assertEquals('ES384', $Cap->GetAlgoToken());

		////////

		try { $Cap->SetAlgoToken('qwertyasdf'); }
		catch(Exception $Err) { }

		$this->assertInstanceOf(Captcha\Error\AlgoUnsupported::class, $Err);

		/////////

		return;
	}

	#[Test]
	public function
	MethodSetIssuer():
	void {

		$Cap = new Captcha;
		$Default = new Captcha\Config;

		$this->assertEquals($Default->Issuer, $Cap->GetIssuer());

		$Cap->SetIssuer('majdak.net');
		$this->assertEquals('majdak.net', $Cap->GetIssuer());

		return;
	}

	#[Test]
	public function
	MethodSetCodeFieldName():
	void {

		$Conf = new Captcha\Config;
		$Test = new Captcha;

		////////

		$this->assertEquals(
			$Conf->FieldNameCode,
			$Test->GetCodeFieldName()
		);

		////////

		$Test->SetCodeFieldName('code-field-name');

		$this->assertEquals(
			'code-field-name',
			$Test->GetCodeFieldName()
		);

		return;
	}

	#[Test]
	public function
	MethodSetTokenFieldName():
	void {

		$Conf = new Captcha\Config;
		$Test = new Captcha;

		////////

		$this->assertEquals(
			$Conf->FieldNameToken,
			$Test->GetTokenFieldName()
		);

		////////

		$Test->SetTokenFieldName('token-field-name');

		$this->assertEquals(
			'token-field-name',
			$Test->GetTokenFieldName()
		);

		return;
	}

	#[Test]
	public function
	MethodGetHash():
	void {

		$Cap = new Captcha;
		$Err = NULL;

		try { $Cap->GetHash(); }
		catch(Exception $Err) { }
		$this->assertInstanceOf(Captcha\Error\NotGenerated::class, $Err);

		$Cap->Generate();
		$this->assertEquals(
			hash($Cap->GetAlgoHash(), $Cap->GetKey()),
			$Cap->GetHash()
		);

		return;
	}

	#[Test]
	public function
	MethodGetWhenIssued():
	void {

		$Now = time();
		$Cap = new Captcha;
		$Err = NULL;

		try { $Cap->GetWhenIssued(); }
		catch(Exception $Err) { }
		$this->assertInstanceOf(Captcha\Error\NotGenerated::class, $Err);

		$Cap->Generate();
		$this->assertGreaterThanOrEqual($Now, $Cap->GetWhenIssued());

		return;
	}

	#[Test]
	public function
	MethodGetWhenExpire():
	void {

		$Now = time();
		$Cap = new Captcha;
		$Err = NULL;

		try { $Cap->GetWhenExpire(); }
		catch(Exception $Err) { }
		$this->assertInstanceOf(Captcha\Error\NotGenerated::class, $Err);

		$Cap->Generate();
		$this->assertGreaterThanOrEqual($Now, $Cap->GetWhenExpire());

		return;
	}

	#[Test]
	public function
	MethodGetPayload():
	void {

		$Cap = new Captcha;
		$Err = NULL;

		////////

		try { $Cap->GetPayload(); }
		catch(Exception $Err) { }
		$this->assertInstanceOf(
			Captcha\Error\NotGenerated::class,
			$Err
		);

		////////

		$Cap->Generate();
		$this->assertInstanceOf(
			Captcha\Payload::class,
			$Cap->GetPayload()
		);

		return;
	}

	#[Test]
	public function
	MethodGetCodeFieldStyleCSS():
	void {

		$Conf = new Captcha\Config;
		$Test = new Captcha;

		$Style = $Test->GetCodeFieldStyleCSS();

		$this->assertTrue(str_contains($Style, sprintf(
			'width:%dpx;', $Conf->ImageWidth
		)));

		return;
	}

	#[Test]
	public function
	MethodGetImageStyleCSS():
	void {

		$Conf = new Captcha\Config;
		$Test = new Captcha;

		$Style = $Test->GetImageStyleCSS();

		$this->assertTrue(str_contains($Style, sprintf(
			'width:%dpx;', $Conf->ImageWidth
		)));

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

		$this->assertTrue(Captcha::Validate(
			$Cap->GetTokenEncoded(), $Cap->GetKey()
		));

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Test]
	public function
	FailBeacuseIncorrectKey():
	void {

		$Err = NULL;

		////////

		$Cap = Captcha::New();
		$Cap->Generate();

		////////

		$this->assertFalse(Captcha::Validate(
			$Cap->GetToken(),
			'chumbawamba'
		));

		////////

		try { Captcha::Validate($Cap->GetToken(), 'fatboy slim', TRUE); }
		catch(Exception $Err) { }

		$this->assertInstanceOf(Captcha\Error\TokenCodeIncorrect::class, $Err);

		return;
	}

	#[Test]
	public function
	FailBeacuseExpired():
	void {

		$Err = NULL;

		////////

		$Cap = Captcha::New();
		$Cap->SetTimeToExpire(-30);
		$Cap->Generate();

		////////

		$this->assertFalse(Captcha::Validate(
			$Cap->GetToken(),
			$Cap->GetKey()
		));

		////////

		try { Captcha::Validate($Cap->GetToken(), $Cap->GetKey(), TRUE); }
		catch(Exception $Err) { }

		$this->assertInstanceOf(Captcha\Error\TokenExpired::class, $Err);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Test]
	public function
	MethodGetToken():
	void {

		$Test = new Captcha;
		$Error = NULL;

		////////

		try { $Test->GetToken(); }
		catch(Exception $Error) { }

		$this->assertInstanceOf(
			Captcha\Error\NotGenerated::class,
			$Error
		);

		////////

		$Test->Generate();
		$Token = $Test->GetToken();
		$this->assertGreaterThan(8, strlen($Token));
		$this->assertEquals(2, substr_count($Token, '.'));

		////////

		$Token = $Test->GetTokenEncoded();
		$this->assertGreaterThan(8, strlen($Token));
		$this->assertEquals(0, substr_count($Test->GetTokenEncoded(), '.'));

		////////

		$Token = $Test->GetTokenForForm();
		$this->assertTrue($Test->Config->Hardened);
		$this->assertEquals('', $Token);

		////////

		$Test->Config->Hardened = FALSE;
		$Token = $Test->GetTokenForForm();
		$this->assertFalse($Test->Config->Hardened);
		$this->assertEquals($Test->GetTokenEncoded(), $Token);

		return;
	}

	#[Test]
	public function
	MethodGetImage():
	void {

		$Test = new Captcha;

		////////

		$Error = NULL;
		try { $Test->GetImageBinary(); }
		catch(Exception $Error) { }

		$this->assertInstanceOf(
			Captcha\Error\NotGenerated::class,
			$Error
		);

		$Error = NULL;
		try { $Test->GetImageEncoded(); }
		catch(Exception $Error) { }

		$this->assertInstanceOf(
			Captcha\Error\NotGenerated::class,
			$Error
		);

		////////

		$Test->Generate();
		$Blob = $Test->GetImageBinary();
		$this->assertTrue(str_starts_with($Blob, "\x89PNG"));

		$Blob = $Test->GetImageEncoded();
		$this->assertTrue(str_starts_with($Blob, 'data:image/png;base64,iVBORw'));

		return;
	}

	#[Test]
	public function
	MethodPushToSession():
	void {

		$Test = new Captcha;
		$Error = NULL;

		////////

		try { $Test->PushToSession()(); }
		catch(Exception $Error) { }
		$this->assertInstanceOf(
			Captcha\Error\NotGenerated::class,
			$Error
		);

		////////

		// test assuming sessions are disabled.
		// force nuke the session global to make sure.

		if(array_key_exists('_SESSION', $GLOBALS))
		unset($GLOBALS['_SESSION']);

		$Test->Generate();
		try { $Test->PushToSession(); }
		catch(Exception $Error) { }
		$this->assertInstanceOf(
			Captcha\Error\NoSession::class,
			$Error
		);

		////////

		// test assuming sessions are enabled.
		// force rebuild the session global to make sure.

		$GLOBALS['_SESSION'] = [];
		$Error = NULL;

		try { $Test->PushToSession(); }
		catch(Exception $Error) { }
		$this->assertNull($Error);

		$this->assertTrue(isset(
			$_SESSION[$Test->GetTokenFieldName()]
		));

		return;
	}

	#[Test]
	public function
	MethodValidateFromInput():
	void {

		if(!array_key_exists('_SESSION', $GLOBALS))
		$GLOBALS['_SESSION'] = [];

		$Test = Captcha::Fresh();

		////////

		// fail because both fields are missing.

		$Data = [];
		$this->assertFalse(Captcha::ValidateFromInput($Data));

		// fail beacuse one field is still empty.

		$Data[$Test->GetTokenFieldName()] = $Test->GetTokenEncoded();
		$this->assertFalse(Captcha::ValidateFromInput($Data));

		// pass because all the data is there.

		$Data[$Test->GetCodeFieldName()] = $Test->GetKey();
		$this->assertTrue(Captcha::ValidateFromInput($Data));


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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Test]
	public function
	MethodFilterOutputToken():
	void {

		$this->assertNull(Captcha::FilterOutputToken(''));
		$this->assertEquals('b2s=', Captcha::FilterOutputToken('ok'));

		return;
	}

	#[Test]
	public function
	MethodFilterInputToken():
	void {

		$this->assertNull(Captcha::FilterInputToken(''));
		$this->assertNull(Captcha::FilterInputToken("\xFF"));
		$this->assertEquals('ok', Captcha::FilterInputToken('b2s='));

		return;
	}

};
