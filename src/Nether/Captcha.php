<?php ##########################################################################
################################################################################

namespace Nether;

use Firebase\JWT;
use Nether\Datastore;
use Nether\Filter;

use Nether\Meta\Code\Info;
use Nether\Meta\Code\Added;
use Exception;

################################################################################
################################################################################

class Captcha {

	public Captcha\Config
	$Config;

	////////////////////////////////////////////////////////////////

	protected string
	$ID;

	protected string
	$Key;

	protected string
	$Hash;

	protected int
	$WhenIssued;

	public int
	$WhenExpires;

	public Captcha\Payload
	$Payload;

	public string
	$Token;

	public Captcha\Image
	$Img;

	////////////////////////////////////////////////////////////////

	public function
	__Construct(?Captcha\Config $Config=NULL) {

		$this->Config = $Config ?? new Captcha\Config;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetKey(?string $Key):
	static {

		if($Key === NULL)
		unset($this->Key);

		else
		$this->Key = $Key;

		////////

		unset($this->ID);
		unset($this->Hash);
		unset($this->WhenIssued);
		unset($this->WhenExpires);
		unset($this->Payload);
		unset($this->Token);
		unset($this->Img);

		return $this;
	}

	public function
	SetAlgoHash(string $Algo):
	static {

		$Supported = Datastore::FromInput(hash_algos());

		////////

		if(!$Supported->HasValue($Algo))
		throw new Captcha\Error\AlgoUnsupported($Algo);

		////////

		$this->Config->AlgoHash = $Algo;

		return $this;
	}

	public function
	SetAlgoToken(string $Algo):
	static {

		$Supported = Datastore::FromInput(JWT\JWT::$supported_algs);

		////////

		if(!$Supported->Has($Algo))
		throw new Captcha\Error\AlgoUnsupported($Algo);

		////////

		$this->Config->AlgoToken = $Algo;

		return $this;
	}

	public function
	SetIssuer(string $Who):
	static {

		$this->Config->Issuer = $Who;

		return $this;
	}

	public function
	SetTimeToExpire(int $Seconds):
	static {

		$this->Config->TimeToExpire = $Seconds;

		return $this;
	}

	public function
	SetCodeFieldName(string $Name):
	static {

		$this->Config->FieldNameCode = $Name;

		return $this;
	}

	public function
	SetTokenFieldName(string $Name):
	static {

		$this->Config->FieldNameToken = $Name;

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetID():
	string {

		if(!isset($this->ID))
		throw new Captcha\Error\NotGenerated;

		return $this->ID;
	}

	public function
	GetKey():
	string {

		return $this->Key;
	}

	public function
	GetAlgoHash():
	string {

		return $this->Config->AlgoHash;
	}

	public function
	GetAlgoToken():
	string {

		return $this->Config->AlgoToken;
	}

	public function
	GetHash():
	string {

		if(!isset($this->Hash))
		throw new Captcha\Error\NotGenerated;

		return $this->Hash;
	}

	public function
	GetIssuer():
	string {

		return $this->Config->Issuer;
	}

	public function
	GetTimeToExpire():
	int {

		return $this->Config->TimeToExpire;
	}

	public function
	GetWhenIssued():
	int {

		if(!isset($this->WhenIssued))
		throw new Captcha\Error\NotGenerated;

		return $this->WhenIssued;
	}

	public function
	GetWhenExpire():
	int {

		if(!isset($this->WhenExpires))
		throw new Captcha\Error\NotGenerated;

		return $this->WhenExpires;
	}

	public function
	GetPayload():
	Captcha\Payload {

		if(!isset($this->Payload))
		throw new Captcha\Error\NotGenerated;

		return $this->Payload;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Info('Returns the configured field name for labeling the input code.')]
	#[Added('2026-11-13')]
	public function
	GetCodeFieldName():
	string {

		return $this->Config->FieldNameCode;
	}

	#[Info('Returns CSS useful for styling the front end input.')]
	#[Added('2026-11-13')]
	public function
	GetCodeFieldStyleCSS():
	string {

		$Style = sprintf(
			'width:%dpx;',
			$this->Config->ImageWidth
		);

		return $Style;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Info('Returns the Token (JWT)')]
	public function
	GetToken():
	string {

		if(!isset($this->Token))
		throw new Captcha\Error\NotGenerated;

		return $this->Token;
	}

	#[Info('Returns the configured field name for labeling the token.')]
	#[Added('2026-11-13')]
	public function
	GetTokenFieldName():
	string {

		return $this->Config->FieldNameToken;
	}

	#[Info('Returns the value forms should send back.')]
	#[Added('2026-11-13')]
	public function
	GetTokenFieldValue():
	string {

		return $this->ID;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetImageBinary():
	string {

		if(!isset($this->Img))
		throw new Captcha\Error\NotGenerated;

		return $this->Img->Render();
	}

	public function
	GetImageEncoded():
	string {

		if(!isset($this->Img))
		throw new Captcha\Error\NotGenerated;

		return sprintf(
			'data:image/png;base64,%s',
			base64_encode($this->Img->Render())
		);
	}

	#[Info('Returns CSS useful for styling the front end image render.')]
	#[Added('2026-11-13')]
	public function
	GetImageStyleCSS():
	string {

		$Style = sprintf(
			'width:%dpx;',
			$this->Config->ImageWidth
		);

		return $Style;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Generate():
	static {

		($this)
		->PrepareKeyHash()
		->PrepareTimeWindow()
		->PreparePayload()
		->PrepareToken()
		->PrepareImage();

		return $this;
	}

	public function
	Destroy():
	static {

		if(isset($this->ID))
		static::SessionTokenUnset($this->ID);

		////////

		$this->SetKey(NULL);

		return $this;
	}

	#[Info('Push the token into the PHP session.')]
	#[Added('2026-11-13')]
	public function
	PushToSession():
	static {

		if(!isset($this->Token))
		throw new Captcha\Error\NotGenerated;

		////////
	
		static::SessionTokenSet($this->ID, $this->Token);

		return $this;
	}

	////////////////////////////////////////////////////////////////

	protected function
	PrepareKeyHash():
	static {

		if(!isset($this->Key))
		$this->Key = static::Key();

		////////

		$this->Hash = hash(
			$this->Config->AlgoHash,
			$this->Key
		);

		$this->ID = static::UUID();

		return $this;
	}

	protected function
	PrepareTimeWindow():
	static {

		$this->WhenIssued = time();
		$this->WhenExpires = $this->WhenIssued + $this->Config->TimeToExpire;

		return $this;
	}

	protected function
	PreparePayload():
	static {

		$this->Payload = new Captcha\Payload(
			Issuer: $this->Config->Issuer,
			IssuedAt: $this->WhenIssued,
			Expires: $this->WhenExpires
		);

		return $this;
	}

	protected function
	PrepareToken():
	static {

		$this->Token = JWT\JWT::encode(
			$this->Payload->ToPayload(),
			$this->Hash,
			$this->Config->AlgoToken
		);

		return $this;
	}

	protected function
	PrepareImage():
	static {

		$this->Img = new Captcha\Image(
			$this->Config->ImageWidth,
			$this->Config->ImageHeight,
			$this->Config->ImageFont,
			$this->Config->ImageWider,
			$this->Key
		);

		$this->Img->Generate();

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Key(?Captcha\Config $Config=NULL):
	string {

		$Config = $Config ?? new Captcha\Config;
		$List = Datastore::FromString($Config->KeyCharList);
		$Min = 0;
		$Max = $List->Count() - 1;
		$Size = NULL;
		$Key = '';

		////////

		for($Size = 0; $Size < $Config->KeyLength; $Size++)
		$Key .= $List->Get(random_int($Min, $Max));

		////////

		return strtolower($Key);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Info('Validate Token can be checked by specified Code.')]
	static public function
	Validate(string $Token, string $Code, bool $ThrowFail=FALSE, ?Captcha\Config $Config=NULL):
	bool {

		$Config = $Config ?? new Captcha\Config;
		$Key = strtolower($Code);
		$Hash = NULL;
		$Err = NULL;

		////////

		if(!str_contains($Token, '.'))
		$Token = base64_decode($Token);

		////////

		try {
			$Hash = hash($Config->AlgoHash, $Key);
			$Ring = new JWT\Key($Hash, $Config->AlgoToken);

			Captcha\Payload::FromPayload(
				JWT\JWT::decode($Token, $Ring)
			);
		}

		catch(JWT\SignatureInvalidException $Err) {
			// signature failure check is a valid response when the
			// wrong key is supplied for that token.
		}

		catch(JWT\ExpiredException $Err) {
			// being expired is a valid response if the user took way
			// too long to submit this data.
		}

		catch(Exception $Err) {

		}

		////////

		if($Err instanceof JWT\SignatureInvalidException) {
			if($ThrowFail)
			throw new Captcha\Error\TokenCodeIncorrect;

			return FALSE;
		}


		if($Err instanceof JWT\ExpiredException) {
			if($ThrowFail)
			throw new Captcha\Error\TokenExpired;

			return FALSE;
		}		

		if($Err instanceof Exception) {
			if($ThrowFail)
			throw $Err;
			
			return FALSE;
		}



		////////

		return TRUE;
	}

	#[Info('Handle validating input that is "raw" from the source.')]
	static public function
	ValidateFromInput(iterable $Input, bool $ThrowFail=FALSE, ?Captcha\Config $Config=NULL):
	bool {

		$Config = $Config ?? new Captcha\Config;

		$Post = Datastore::FromInput($Input, [
			$Config->FieldNameToken => Filter\Text::TrimmedNullable(...),
			$Config->FieldNameCode  => Filter\Text::TrimmedNullable(...)
		]);

		////////

		if(!$Post->Get($Config->FieldNameToken)) {
			if($ThrowFail)
			throw new Captcha\Error\TokenExpired;
			
			return FALSE;
		}

		if(!$Post->Get($Config->FieldNameCode)) {
			if($ThrowFail)
			throw new Captcha\Error\TokenCodeIncorrect;

			return FALSE;
		}

		$TID = $Post->Get($Config->FieldNameToken);
		$TCode = $Post->Get($Config->FieldNameCode);
		$TValue = static::SessionTokenGet($TID);

		////////

		return static::Validate($TValue, $TCode, $ThrowFail, $Config);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(?Captcha\Config $Config=NULL):
	static {

		$Output = new static($Config);

		return $Output;
	}

	static public function
	Fresh(?Captcha\Config $Config=NULL):
	static {

		$Output = new static($Config);
		$Output->Generate();

		if(isset($_SESSION))
		$Output->PushToSession();		

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static function
	UUID():
	string {

		$Bytes = random_bytes(16);
		$Bytes[6] = chr(ord($Bytes[6]) & 0x0f | 0x40);
		$Bytes[8] = chr(ord($Bytes[8]) & 0x3f | 0x80);

		return sprintf(
			'%s%s-%s-%s-%s-%s%s%s',
			... str_split(bin2hex($Bytes), 4)
		);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// these are the methods that abstract the rest of the library from
	// the session storage of these tokens. adding support for alternate
	// storage locations should be just editing these.

	static function
	SessionTokenStorageInit():
	void {

		if(!isset($_SESSION))
		$GLOBALS['_SESSION'] = [];

		if(!isset($_SESSION['NetherCaptchaTokens']))
		$_SESSION['NetherCaptchaTokens'] = [];

		return;
	}

	static function
	SessionTokenGet(string $ID):
	string|FALSE {

		static::SessionTokenStorageInit();

		////////

		if(!array_key_exists($ID, $_SESSION['NetherCaptchaTokens']))
		return FALSE;

		return $_SESSION['NetherCaptchaTokens'][$ID];
	}

	static function
	SessionTokenSet(string $ID, string $Token):
	void {

		static::SessionTokenStorageInit();

		////////

		$_SESSION['NetherCaptchaTokens'][$ID] = $Token;

		return;
	}

	static function
	SessionTokenUnset(string $ID):
	void {

		static::SessionTokenStorageInit();

		////////

		unset($_SESSION['NetherCaptchaTokens'][$ID]);

		return;
	}

};


