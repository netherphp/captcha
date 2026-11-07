<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

use Nether\Datastore;
use Nether\Filter;

use Exception;

################################################################################
################################################################################

class Payload {

	public string
	$Issuer;

	public int
	$IssuedAt;

	public int
	$Expires;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(string $Issuer, int $IssuedAt, int $Expires) {

		$this->Issuer = $Issuer;
		$this->IssuedAt = $IssuedAt;
		$this->Expires = $Expires;

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	
	public function
	IsExpired(int $When):
	bool {

		return ($this->Expires < $When);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ToPayload():
	array {

		return [
			'iss' => $this->Issuer,
			'iat' => $this->IssuedAt,
			'exp' => $this->Expires
		];
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////
	
	static public function
	FromPayload(object $Input):
	static {

		$Decode = Datastore::FromInput(get_object_vars($Input), [
			'iss' => Filter\Text::Trimmed(...),
			'iat' => Filter\Type::Int(...),
			'exp' => Filter\Type::Int(...)
		]);

		if(!$Decode->HasAll('iss', 'iat', 'exp'))
		throw new Exception('invalid payload format');

		////////

		$Output = new static(
			Issuer: $Decode['iss'],
			IssuedAt: $Decode['iat'],
			Expires: $Decode['exp']
		);

		////////

		return $Output;
	}

};
