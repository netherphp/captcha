<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

################################################################################
################################################################################

class Config {

	public string
	$AlgoHash = 'sha512';

	public string
	$AlgoToken = 'HS512';

	public string
	$Issuer = 'netherphp/captcha';

	public int
	$TimeToExpire = 180;

	public int
	$KeyLength = 8;

};
