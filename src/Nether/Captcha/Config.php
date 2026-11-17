<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

use Nether\Meta\Code\Info;

################################################################################
################################################################################

class Config {

	const
	FontDefault = 'neon-sans',
	FontBold    = 'nouveau-ibm',
	FontCrisp   = 'xanmono';

	const
	FontDefaultCharset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
	FontBoldCharset    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
	FontCrispCharset   = 'ABCDEFGHIJKLMNPRTUVWXYZ12346789';

	public string
	$AlgoHash = 'sha512';

	public string
	$AlgoToken = 'HS512';

	public string
	$Issuer = 'netherphp/captcha';

	public int
	$TimeToExpire = 240;

	public string
	$KeyCharList = self::FontCrispCharset;

	public int
	$KeyLength = 6;

	public bool
	$ImageWider = TRUE;

	public string
	$ImageFont = self::FontCrisp;

	public int
	$ImageWidth = 200;

	public int
	$ImageHeight = 40;

	public string
	$FieldNameCode = 'nether-captcha-code';

	public string
	$FieldNameToken = 'nether-captcha-token';

};
