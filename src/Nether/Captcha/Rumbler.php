<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

################################################################################
################################################################################

class Rumbler {

	public float
	$X = 0.0;

	public float
	$Y = 0.0;

	public float
	$Scale = 1.0;

	public float
	$Rotate = 0.0;

	public function
	Bump():
	void {

		($this)
		->BumpPosition()
		->BumpRotation()
		->BumpScale();

		return;
	}

	public function
	BumpPosition(float $Scale=1.0):
	static {

		$X = random_int(-1, 1)?-1:1 * $Scale;
		$Y = random_int(-1, 1)?-1:1 * $Scale;

		if($this->X === $X)
		$X *= -1;

		if($this->Y === $Y)
		$Y *= -1;

		$this->X = $X;
		$this->Y = $Y;

		return $this;
	}

	public function
	BumpRotation(float $Scale=1.0):
	static {

		$FloatRNG = (fn()=>  (random_int(50, 100) / 10.0) * random_int(0, 1) ? -1.0 : 1.0 );

		$R = $FloatRNG() * $Scale;

		if($this->Rotate > 0 && $R > 0)
		$R *= -1;

		$this->Rotate = $R;

		return $this;
	}

	public function
	BumpScale(float $Scale=1.0):
	static {

		$FloatRNG = (fn()=>  (random_int(0, 10) / 100.0));

		$S = (1.0 + $FloatRNG()) * $Scale;

		$this->Scale = $S;

		return $this;
	}

	////////////////////////////////////////////////////////////////

	public function
	SetPosition(int $X, int $Y):
	static {

		$this->X = $X;
		$this->Y = $Y;

		return $this;
	}

	public function
	SetRotation(float $Rot):
	static {

		$this->Rotate = $Rot;

		return $this;
	}

	public function
	SetScale(float $Scale):
	static {

		$this->Scale = $Scale;

		return $this;
	}

};
