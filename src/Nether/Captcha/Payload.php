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
	__Construct(string $Issuer='', int $IssuedAt=-1, int $Expires=-1) {

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

		$Decode = Datastore::FromObject($Input);
		$Decode->SetValueFilter('iss', Filter\Text::Trimmed(...));
		$Decode->SetValueFilter('iat', Filter\Type::Int(...));
		$Decode->SetValueFilter('exp', Filter\Type::Int(...));

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
