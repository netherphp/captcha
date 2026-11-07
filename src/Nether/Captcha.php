<?php ##########################################################################
################################################################################

namespace Nether;

use Firebase\JWT;
use Nether\Datastore;
use Nether\Filter;
use Exception;

################################################################################
################################################################################

class Captcha {

	protected string
	$AlgoToken;

	protected string
	$AlgoHash;

	protected int
	$TimeToExpire;

	protected string
	$Issuer;

	////////////////////////////////////////////////////////////////

	protected ?string
	$Key;

	protected string
	$Hash;

	protected int
	$WhenIssued;
	
	public int
	$WhenExpires;

	public Datastore
	$Payload;

	public string
	$Token;

	////////////////////////////////////////////////////////////////

	public function
	__Construct() {

		$Config = static::Config();

		$this->SetAlgoHash($Config['AlgoHash']);
		$this->SetAlgoToken($Config['AlgoToken']);
		$this->SetTimeToExpire($Config['TimeToExpire']);
		$this->SetIssuer($Config['Issuer']);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	SetAlgoHash(string $Algo):
	static {

		$this->AlgoHash = $Algo;

		return $this;
	}

	public function
	SetAlgoToken(string $Algo):
	static {

		$this->AlgoToken = $Algo;

		return $this;
	}

	public function
	SetKey(?string $Key):
	static {

		$this->Key = $Key;

		return $this;
	}

	public function
	SetIssuer(string $Who):
	static {

		$this->Issuer = $Who;

		return $this;
	}

	public function
	SetTimeToExpire(int $Seconds):
	static {

		$this->TimeToExpire = $Seconds;

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
	Datastore {

		return $this->Payload;
	}

	public function
	GetToken():
	string {

		return $this->Token;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	PrepareKeyHash():
	static {

		if(!isset($this->Key))
		$this->Key = static::Key();

		////////
		
		$this->Hash = hash($this->AlgoHash, $this->Key);

		return $this;
	}

	protected function
	PrepareTimeWindow():
	static {

		$this->WhenIssued = time();
		$this->WhenExpires = $this->WhenIssued + $this->TimeToExpire;

		return $this;
	}

	protected function
	PreparePayload():
	static {

		$this->Payload = Datastore::New([
			'iss' => $this->Issuer,
			'iat' => $this->WhenIssued,
			'exp' => $this->WhenExpires
		]);

		return $this;
	}

	protected function
	PrepareToken():
	static {

		$this->Token = JWT\JWT::encode(
			$this->Payload->Export(),
			$this->Hash,
			$this->AlgoToken
		);

		return $this;
	}

	public function
	Generate():
	static {

		($this)
		->PrepareKeyHash()
		->PrepareTimeWindow()
		->PreparePayload()
		->PrepareToken();

		return $this;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Config(?iterable $Input=NULL):
	Datastore {

		$Config = Datastore::New([
			'AlgoToken'    => 'HS512',
			'AlgoHash'     => 'sha512',
			'TimeToExpire' => 360,
			'Issuer'       => 'netherphp/captcha'
		]);

		if($Input !== NULL)
		$Config->Merge($Input);

		return $Config;
	}

	////////////////////////////////////////////////////////////////

	static public function
	Key(int $Length=8):
	string {

		$Key = '';

		for($Size = 0; $Size < $Length; $Size++)
		$Key .= match(TRUE) {
			(random_int(0, 1) === 1)
			=> range('0', '9')[random_int(0, 9)],

			default
			=> range('A', 'Z')[random_int(0, 25)]
		};

		return $Key;
	}

	static public function
	Validate(string $Token, string $Key, ?iterable $Cfg=NULL):
	?bool {

		$Config = static::Config($Cfg);
		$Now = time();
		$Hash = NULL;
		$Err = NULL;

		$Decode = new Datastore;
		$Decode->SetImportFilter('iss', Filter\Text::Trimmed(...));
		$Decode->SetImportFilter('iat', Filter\Type::Int(...));
		$Decode->SetImportFilter('exp', Filter\Type::Int(...));

		// try to decode the token they gave us with the key they gave us.

		try {
			$Hash = hash($Config['AlgoHash'], $Key);
			$Ring = new JWT\Key($Hash, $Config['AlgoToken']);

			$Decode->Import(get_object_vars(
				JWT\JWT::decode($Token, $Ring)
			));
		}

		catch(JWT\SignatureInvalidException $Err) {
			// signature failure check is the correct response when the
			// wrong key is supplied for that token.
		}

		////////

		// decode error.

		if($Err !== NULL)
		return FALSE;

		// missing expire time error.

		if(!$Decode->Has('exp'))
		return FALSE;

		// time expired error.

		if($Decode['exp'] < $Now)
		return FALSE;

		////////		

		return TRUE;
	}

};


