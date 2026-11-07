<?php ##########################################################################
################################################################################

namespace Nether\Captcha;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use Nether\Datastore;
use Nether\Captcha;

################################################################################
################################################################################

class Image {

	protected int
	$Width;

	protected int
	$Height;

	protected string
	$Font;

	protected string
	$Text;

	protected Imagick
	$Img;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__Construct(int $Width, int $Height, string $Font, string $Text) {

		$this->Width = $Width;
		$this->Height = $Height;
		$this->Font = $Font;
		$this->Text = $Text;

		//$this->Text = '0O1I';

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetPathToFont(string $Basename):
	string {

		$Path = sprintf(
			'%s%sfonts%s%s',
			dirname(__FILE__, 4),
			DIRECTORY_SEPARATOR,
			DIRECTORY_SEPARATOR,
			$Basename
		);

		return $Path;
	}

	public function
	Generate():
	static {

		$Rumbler = new Rumbler;

		$this->Img = new Imagick;
		$this->Img->newImage($this->Width, $this->Height, 'black');
		$this->Img->setImageFormat('png');

		$RGB = new Datastore([
			fn()=> $this->GenerateDrawTextLayer(
				Captcha::Key(), $this->Font, '#FF000033',
				$Rumbler->BumpPosition(1.0)->BumpRotation(2.0)->BumpScale(2.0)
			),
			fn()=> $this->GenerateDrawTextLayer(
				Captcha::Key(), $this->Font, '#00FF0033',
				$Rumbler->BumpPosition(1.0)->BumpRotation(2.0)->BumpScale(2.0)
			),
			fn()=> $this->GenerateDrawTextLayer(
				Captcha::Key(), $this->Font, '#0000FF33',
				$Rumbler->BumpPosition(1.0)->BumpRotation(2.0)->BumpScale(2.0)
			)
		]);

		$CYM = new Datastore([
			fn()=> $this->GenerateDrawTextLayer(
				$this->Text, $this->Font, '#00FFFF44',
				$Rumbler->SetPosition(0, 0)->SetRotation(0.0)->SetScale(1.0)
			),
			fn()=> $this->GenerateDrawTextLayer(
				$this->Text, $this->Font, '#FFFF0044',
				$Rumbler->SetPosition(-3, -3)->SetRotation(-1.0)->SetScale(1.0)
			),
			fn()=> $this->GenerateDrawTextLayer(
				$this->Text, $this->Font, '#FF00FF44',
				$Rumbler->SetPosition(0, -3)->SetRotation(-2.0)->SetScale(1.0)
			),
			fn()=> $this->GenerateDrawTextLayer(
				$this->Text, $this->Font, '#00FFFF44',
				$Rumbler->SetPosition(3, -3)->SetRotation(0.0)->SetScale(1.0)
			),
			fn()=> $this->GenerateDrawTextLayer(
				$this->Text, $this->Font, '#FFFF0044',
				$Rumbler->SetPosition(3, 0)->SetRotation(1.0)->SetScale(1.0)
			),
			fn()=> $this->GenerateDrawTextLayer(
				$this->Text, $this->Font, '#FF00FF44',
				$Rumbler->SetPosition(3, 3)->SetRotation(2.0)->SetScale(1.0)
			)
		]);

		$RGB->Shuffle()->Each(function(callable $Func) {
			$Func();
			return;
		});	

		$this->Img->modulateImage(100.0, 75.0, 1.0);
		$this->Img->addNoiseImage(Imagick::NOISE_GAUSSIAN, Imagick::CHANNEL_RED);
		$this->Img->addNoiseImage(Imagick::NOISE_GAUSSIAN, Imagick::CHANNEL_GREEN);
		$this->Img->addNoiseImage(Imagick::NOISE_GAUSSIAN, Imagick::CHANNEL_BLUE);

		$CYM->Shuffle()->Each(function(callable $Func) {
			$Func();
			return;
		});

		return $this;
	}

	public function
	Write(string $Filename):
	static {

		$this->Img->writeImage($Filename);

		return $this;
	}

	public function
	Render():
	string {

		$this->Img->setImageCompressionQuality(95); 
		$this->Img->setOption('png:compression-level', '9');

		return $this->Img->getImageBlob();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	GenerateDrawTextLayer(string $Text, string $FontName, string $Colour, Rumbler $Rumbler):
	void {

		if(!str_contains($FontName, '.'))
		$FontName .= '.ttf';

		////////

		$Font = new ImagickDraw;
		$Font->setFont($this->GetPathToFont($FontName));
		$Font->setFontSize($this->Height /2);
		$Font->setFillColor(new ImagickPixel($Colour));
		$Font->setGravity(Imagick::GRAVITY_NORTHWEST);

		$Size = $this->Img->queryFontMetrics($Font, $Text);

		////////

		$Tmp = new Imagick;

		$Tmp->newImage(
			$Size['textWidth'],
			$Size['textHeight'],
			new ImagickPixel('transparent')
		);

		$Tmp->annotateImage(
			$Font,
			0, 0, 0,
			$Text
		);

		$Tmp->resizeImage(
			(int)($this->Width * (0.9 * $Rumbler->Scale)),
			(int)($this->Height * (0.9 * $Rumbler->Scale)),
			Imagick::FILTER_LANCZOS2, 0.0,
			TRUE
		);

		$Tmp->rotateImage('transparent', $Rumbler->Rotate);

		////////

		$this->Img->compositeImage(
			$Tmp, Imagick::COMPOSITE_BLEND,
			(int)(($this->Width - $Tmp->getImageWidth()) / 2) + $Rumbler->X,
			(int)(($this->Height - $Tmp->getImageHeight()) / 2) + $Rumbler->Y
		);

		return;
	}

};
