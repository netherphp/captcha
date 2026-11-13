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

		unset($this->Hash);
		unset($this->WhenIssued);
		unset($this->WhenExpires);
		unset($this->Payload);
		unset($this->Token);

		return $this;
	}

	public function
	SetAlgoHash(string $Algo):
	static {

		$this->Config->AlgoHash = $Algo;

		return $this;
	}

	public function
	SetAlgoToken(string $Algo):
	static {

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetKey():
	string {

		return $this->Key;
	}

	public function
	GetHash():
	string {

		return $this->Hash;
	}

	public function
	GetWhenIssued():
	int {

		return $this->WhenIssued;
	}

	public function
	GetWhenExpire():
	int {

		return $this->WhenExpires;
	}

	public function
	GetPayload():
	Captcha\Payload {

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

		return $this->Token;
	}

	#[Info('Returns Base64 encoded version of the token.')]
	#[Added('2026-11-13')]
	public function
	GetTokenEncoded():
	string {

		return base64_encode($this->GetToken());
	}

	#[Info('Returns GetTokenEncoded but only if not hardened so that the front end can think less.')]
	#[Added('2026-11-13')]
	public function
	GetTokenForForm():
	string {

		if($this->Config->Hardened)
		return '';

		return $this->GetTokenEncoded();
	}

	#[Info('Returns the configured field name for labeling the token.')]
	#[Added('2026-11-13')]
	public function
	GetTokenFieldName():
	string {

		return $this->Config->FieldNameToken;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetImageBinary():
	string {

		return $this->Img->Render();
	}

	public function
	GetImageEncoded():
	string {

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

	#[Info('Push the token into the PHP session.')]
	#[Added('2026-11-13')]
	public function
	PushToSession():
	static {

		if(!isset($this->Token))
		throw new Captcha\Error\NotGenerated;

		if(!is_array($_SESSION))
		throw new Captcha\Error\NoSession;

		////////

		$Output = static::FilterOutputToken($this->Token);

		$_SESSION[$this->Config->FieldNameToken] = $Output;

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
	Validate(string $Token, string $Code, ?Captcha\Config $Config=NULL):
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

		////////

		if($Err instanceof JWT\SignatureInvalidException)
		return FALSE;

		if($Err instanceof JWT\ExpiredException)
		return FALSE;

		////////

		return TRUE;
	}

	#[Info('Handle validating input that is "raw" from the source.')]
	static public function
	ValidateFromInput(iterable $Input, ?Captcha\Config $Config=NULL):
	bool {

		$Config = $Config ?? new Captcha\Config;
		$Session = Datastore::FromInput($_SESSION);

		$Post = Datastore::FromInput($Input, [
			$Config->FieldNameToken
			=> static::FilterInputToken(...),
			$Config->FieldNameCode
			=> Filter\Text::TrimmedNullable(...)
		]);

		// when in hardened mode the only trusted source of the token
		// is from the PHP session.

		if($Config->Hardened) {
			($Post)
			->Unset($Config->FieldNameToken)
			->Set(
				$Config->FieldNameToken,
				$Session->Get($Config->FieldNameToken)
			);
		}

		////////

		if(!$Post->Get($Config->FieldNameToken))
		return FALSE;

		if(!$Post->Get($Config->FieldNameCode))
		return FALSE;

		////////

		return static::Validate(
			$Post[$Config->FieldNameToken],
			$Post[$Config->FieldNameCode],
			$Config
		);
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

		if($Output->Config->Hardened)
		$Output->PushToSession();

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Info('Generate output to treat as invalidated input later.')]
	static public function
	FilterOutputToken(string $Token):
	?string {

		$Out = base64_encode($Token);

		if(!$Out)
		return NULL;

		return $Out;
	}

	#[Info('Treat input as unvalidated input.')]
	static public function
	FilterInputToken(string $Input):
	?string {

		$Out = Filter\Text::TrimmedNullable($Input);

		if(!$Out)
		return NULL;

		////////

		if(is_string($Out))
		$Out = base64_decode($Out, TRUE);

		if(!$Out)
		return NULL;

		////////

		return $Out;
	}

};


