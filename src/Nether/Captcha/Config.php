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
	FontCrispCharset   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ346789';

	#[Info('Hardened mode will not send the Token to the client, but would mess with someone if they had multiple tabs open.')]
	public bool
	$Hardened = TRUE;

	public string
	$AlgoHash = 'sha512';

	public string
	$AlgoToken = 'HS512';

	public string
	$Issuer = 'netherphp/captcha';

	public int
	$TimeToExpire = 180;

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
	$FieldNameToken = 'nether-catpcha-token';

};
