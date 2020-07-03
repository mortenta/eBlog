<?php
class blog_gd {
	
	
	private $ImgStr;
	private $ImageType;
	private $OrigWidth;
	private $OrigHeight;
	private $NewWidth;
	private $NewHeight;
	
	private $Img;
	
	private $OutFilepath;
	
	function __construct () {
		
	}
	
	// Setters
	public function setImgStr ($string) {
		$this->ImgStr = $string;
		return TRUE;
	}
	
	public function setImageType ($string) {
		$this->ImageType = $string;
		return TRUE;
	}

	public function setNewWidth ($int) {
		if (is_numeric($int)) {
			$this->NewWidth = $int;
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	public function setNewHeight ($int) {
		if (is_numeric($int)) {
			$this->NewHeight = $int;
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	// Getters
	
	public function isLandscape () {
		if (is_resource($this->Img)) {
			$this->getDimensions();
			if ($this->OrigWidth >= $this->OrigHeight) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	public function getOrigWidth () {
		return $this->OrigWidth;
	}
	
	public function getOrigHeight () {
		return $this->OrigHeight;
	}

	public function getNewWidth () {
		return $this->NewWidth;
	}
	
	public function getNewHeight () {
		return $this->NewHeight;
	}
	
	// Public methods
	
	function create () {
		if (is_string($this->ImgStr)) {
			if (is_resource($this->Img = imagecreatefromstring($this->ImgStr))) {
				$this->getDimensions();
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	function resize () {
		if (is_resource($this->Img)) {
			if (!(is_numeric($this->OrigWidth) && is_numeric($this->OrigHeight))) {
				$this->getDimensions();
			}
			if (is_numeric($this->NewWidth) && is_numeric($this->NewHeight)) {
				$NW = $this->NewWidth;
				$NH = $this->NewHeight;
			}
			elseif (is_numeric($this->NewWidth)) {
				$NW = $this->NewWidth;
				$NH = round($this->OrigHeight/$this->OrigWidth*$this->NewWidth);
				$this->NewHeight = $NH;
			}
			elseif (is_numeric($this->NewHeight)) {
				$NW = round($this->OrigWidth/$this->OrigHeight*$this->NewHeight);
				$NH = $this->NewHeight;
				$this->NewWidth = $NW;
			}
			if (is_numeric($NW) && is_numeric($NH)) {
				$NewImg = imagecreatetruecolor($NW,$NH);
				if ($this->ImageType == 'gif') {
					$TranspIndex = imagecolortransparent($this->Img);
					if ($TranspIndex >= 0) {
						$TranspColor = imagecolorsforindex($this->Img,$TranspIndex);
						unset($TranspIndex);
						$TranspIndex = imagecolorallocate($NewImg,$TranspColor['red'],$TranspColor['green'],$TranspColor['blue']);
						imagefill($NewImg,0,0,$TranspIndex);
						imagecolortransparent($NewImg,$TranspIndex);
					}
				}
				elseif ($this->ImageType == 'png') {
					imagealphablending($NewImg,false);
					$PngColor = imagecolorallocatealpha($NewImg,0,0,0,127);
					imagefill($NewImg,0,0,$PngColor);
					imagesavealpha($NewImg,true);
				}
				imageantialias($NewImg,true);
				imagecopyresampled($NewImg,$this->Img,0,0,0,0,$NW,$NH,$this->OrigWidth,$this->OrigHeight);
				$this->Img = $NewImg;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	function display () {
		if (is_resource($this->Img)) {
			header('Content-Type: image/png');
			imagepng($this->Img);
			imagedestroy($this->Img);
		}
	}
	
	function saveFile ($Filepath) {
		if (is_resource($this->Img) && is_dir(dirname($Filepath))) {
			if (imagejpeg($this->Img,$Filepath,80)) {
				imagedestroy($this->Img);
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	public function returnFile () {
		if (is_resource($this->Img)) {
			ob_start();
			if ($this->ImageType == 'gif') {
				@imagegif($this->Img);
			}
			elseif ($this->ImageType == 'png') {
				@imagepng($this->Img,NULL,5);
			}
			else {
				@imagejpeg($this->Img,NULL,75);
			}
			$fs = ob_get_clean();
			if (is_string($fs)) {
				@imagedestroy($this->Img);
				return $fs;
			}
			else {
				$this->InitObj->logErr('',__FILE__,__LINE__,__FUNCTION__,__CLASS__,debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
				return FALSE;
			}
		}
		else {
			$this->InitObj->logErr('',__FILE__,__LINE__,__FUNCTION__,__CLASS__,debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
			return FALSE;
		}
	}
	
	public function getBGColor ($Data=FALSE) {
		if ($Data) {
			$this->ImgStr = $Data;
			$this->create();
		}
		if (is_resource($this->Img)) {
			$rgb = imagecolorat($this->Img,2,2);
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = $rgb & 0xFF;
			$color = sprintf("#%02x%02x%02x", $r, $g, $b);
			if (is_string($color)) {
				return $color;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	
	// Private methods
	
	private function getDimensions () {
		if (is_resource($this->Img)) {
			if (!is_numeric($this->OrigWidth)) {
				$this->OrigWidth = imagesx($this->Img);
			}
			if (!is_numeric($this->OrigHeight)) {
				$this->OrigHeight = imagesy($this->Img);
			}
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
	
}