<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

use Nether\Atlantis;
use Nether\Common;

################################################################################
################################################################################

class Library
extends Common\Library {

	public function
	OnPrepare(...$Argv):
	void {
	/*//
	after the framework that loaded this has determined the environment
	and its required setup.
	//*/

		/** @var Atlantis\Engine $App */

		$App = $Argv['App'];

		return;
	}

};
