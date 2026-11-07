<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

################################################################################
################################################################################

class Config {

	const
	FontDefault = 'neon-sans',
	FontBold    = 'nouveau-ibm',
	FontCrisp   = 'xanmono';

	public string
	$AlgoHash = 'sha512';

	public string
	$AlgoToken = 'HS512';

	public string
	$Issuer = 'netherphp/captcha';

	public int
	$TimeToExpire = 180;

	public string
	$KeyCharList = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	public int
	$KeyLength = 6;

	public string
	$ImageFont = self::FontDefault;

	public int
	$ImageWidth = 200;

	public int
	$ImageHeight = 60;

};
