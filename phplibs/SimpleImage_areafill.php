<?php
//maxareafill(寬, 高, $red , $green, $blue); 等比例+自動填充背景色
//maxareafill(寬, 高); 等比例+自動填充透明
//$img = new SimpleImage($file_tmp);
//$img->maxareafill(660,660,255,255,255); //xareafill(長,寬, 色碼, 色碼, 色碼)
//$img->save($upload_dir.$file_name);	
//$img->maxareafill(972,660); //xareafill(長,寬, 色碼, 色碼, 色碼)
//$file_name = gen_uuid().".png";
//$img->save($upload_dir.$file_name,"IMAGETYPE_PNG");	
// 106.07.04 //不墊底圖方式, 將圖片填充到目標寬高
//minareafill($width, $height) //圖片取中間區塊
//minareafill($width, $height,"top") //直式圖片取上方區塊, 通常用在人像
					
class SimpleImage {
   
	public $image;
	public $image_type;
	public function __construct($filename = null){
		if (!empty($filename)) {
			$this->load($filename);
		}
	}
	public function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		if ($this->image_type == IMAGETYPE_JPEG) {
			$this->image = imagecreatefromjpeg($filename);
		} elseif ($this->image_type == IMAGETYPE_GIF) {
			$this->image = imagecreatefromgif($filename);
		} elseif ($this->image_type == IMAGETYPE_PNG) {
			$this->image = imagecreatefrompng($filename);
		} elseif ($this->image_type == 18) {
			$this->image = imagecreatefromwebp($filename);
		} else {
			throw new Exception("The file you're trying to open is not supported");
		}
	}
	public function save($filename, $image_type = "IMAGETYPE_JPEG", $compression = 80, $permissions = null) {
		//$image_type = $this->image_type;
		if (strpos ($filename, ".png") !== false) {
			$image_type = "IMAGETYPE_PNG"; //2021.09.08
		} 

		if ($image_type == "IMAGETYPE_JPEG") {
			imagejpeg($this->image,$filename,$compression);
		} elseif ($image_type == "IMAGETYPE_GIF") {
			imagegif($this->image,$filename);         
		} elseif ($image_type == "IMAGETYPE_PNG") {
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);			
			imagepng($this->image,$filename);
		}
		if ($permissions != null) {
			chmod($filename,$permissions);
		}
	}
	/*	
		public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null) {
			$image_type = $this->image_type;
			if ($image_type == IMAGETYPE_JPEG) {
				imagejpeg($this->image,$filename,$compression);
			} elseif ($image_type == IMAGETYPE_GIF) {
				imagegif($this->image,$filename);         
			} elseif ($image_type == IMAGETYPE_PNG) {
				imagepng($this->image,$filename);
			}
			if ($permissions != null) {
				chmod($filename,$permissions);
			}
		}
	*/	
	public function output($image_type=IMAGETYPE_JPEG, $quality = 80) {
		if ($image_type == IMAGETYPE_JPEG) {
			header("Content-type: image/jpeg");
			imagejpeg($this->image, null, $quality);
		} elseif ($image_type == IMAGETYPE_GIF) {
			header("Content-type: image/gif");
			imagegif($this->image);         
		} elseif ($image_type == IMAGETYPE_PNG) {
			header("Content-type: image/png");
			imagepng($this->image);
		}
	}
	public function getWidth() {
		return imagesx($this->image);
	}
	public function getHeight() {
		return imagesy($this->image);
	}
	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = round($this->getWidth() * $ratio);
		$this->resize($width,$height);
	}
	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = round($this->getHeight() * $ratio);
		$this->resize($width,$height);
	}
	public function square($size) {
		$new_image = imagecreatetruecolor($size, $size);
		if ($this->getWidth() > $this->getHeight()) {
			$this->resizeToHeight($size);
			
			imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			imagecopy($new_image, $this->image, 0, 0, ($this->getWidth() - $size) / 2, 0, $size, $size);
		} else {
			$this->resizeToWidth($size);
			
			imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
			imagecopy($new_image, $this->image, 0, 0, 0, ($this->getHeight() - $size) / 2, $size, $size);
		}
		$this->image = $new_image;
	}
   
	public function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getHeight() * $scale/100; 
		$this->resize($width,$height);
	}
   
	public function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		
		imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
		
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;   
	}
    public function cut($x, $y, $width, $height) {
    	$new_image = imagecreatetruecolor($width, $height);	
		imagecolortransparent($new_image, imagecolorallocate($new_image, 0, 0, 0));
		imagealphablending($new_image, false);
		imagesavealpha($new_image, true);
		imagecopy($new_image, $this->image, 0, 0, $x, $y, $width, $height);
		$this->image = $new_image;
	}
	public function maxarea($width, $height = null)	{
		$height = $height ? $height : $width;
		
		if ($this->getWidth() > $width) {
			$this->resizeToWidth($width);
		}
		if ($this->getHeight() > $height) {
			$this->resizeToheight($height);
		}
	}
	
	public function minarea($width, $height = null)	{
		$height = $height ? $height : $width;
		
		if ($this->getWidth() < $width) {
			$this->resizeToWidth($width);
		}
		if ($this->getHeight() < $height) {
			$this->resizeToheight($height);
		}
	}
	public function cutFromCenter($width, $height) {
		
		if ($width < $this->getWidth() && $width > $height) {
			$this->resizeToWidth($width);
		}
		if ($height < $this->getHeight() && $width < $height) {
			$this->resizeToHeight($height);
		}
		
		$x = ($this->getWidth() / 2) - ($width / 2);
		$y = ($this->getHeight() / 2) - ($height / 2);
		
		return $this->cut($x, $y, $width, $height);
	}
	public function minareafill($width, $height,$valign="middle") {
	    $this->minarea($width, $height);
		
		if ($this->getWidth() > $this->getHeight()){
			if($this->getHeight() / $this->getWidth() > $height / $width){
				$this->resizeToWidth($width);
			}else{
				$this->resizeToHeight($height); 
			}	
			$x = ($this->getWidth() - $width) / 2;
			$y = ($this->getHeight() - $height) / 2;				
			if($this->getHeight() / $this->getWidth() > $height / $width){
				$this->cut($x,$y,$width, $height); 
			}else{
				$this->cut($x,0,$width, $height);
			}
		}else{
			if($this->getHeight() / $this->getWidth() > $height / $width){
				$this->resizeToWidth($width);
			}else{
				$this->resizeToHeight($height); 
			}		
			$x = ($this->getWidth() - $width) / 2;
			$y = ($this->getHeight() - $height) / 2;			
			if($valign == "middle"){				
				if($this->getHeight() / $this->getWidth() > $height / $width){
					$this->cut($x,$y,$width, $height); 
				}else{
					$this->cut($x,0,$width, $height);
				}
			}else{
				$this->cut($x,0,$width, $height); 
			}
			

		}

	}	
	
	public function maxareafill($width, $height, $red="" , $green="" , $blue="") {
	    $this->maxarea($width, $height);
		$new_image = imagecreatetruecolor($width, $height);
	
		if($red == ""){
			imagealphablending($new_image, false);
			$color_fill = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
			imagefill($new_image, 0, 0, $color_fill);
			imagesavealpha($new_image, true);
		}else{
			$color_fill = imagecolorallocate($new_image, $red, $green, $blue);
			imagefill($new_image, 0, 0, $color_fill); 			
		}	
	    imagecopyresampled(	$new_image, 
	    					$this->image, 
	    					floor(($width - $this->getWidth())/2), 
	    					floor(($height-$this->getHeight())/2), 
	    					0, 0, 
	    					$this->getWidth(), 
	    					$this->getHeight(), 
	    					$this->getWidth(), 
	    					$this->getHeight()
	    				); 
	    $this->image = $new_image;
	}
	

}

?>
