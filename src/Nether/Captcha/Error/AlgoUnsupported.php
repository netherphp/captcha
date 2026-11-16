<?php ##########################################################################
################################################################################

namespace Nether\Captcha\Error;

use Exception;

################################################################################
################################################################################

class AlgoUnsupported
extends Exception {

	public function
	__construct(string $Algo) {

		parent::__construct(sprintf(
			'unsupported algo: %s', $Algo
		));

		return;
	}

};
