<?php ##########################################################################
################################################################################

namespace Nether\Captcha\Error;

use Exception;

################################################################################
################################################################################

class TokenCodeIncorrect
extends Exception {

	public function
	__construct() {

		parent::__construct('token cannot be unlocked with supplied code.');

		return;
	}

};
