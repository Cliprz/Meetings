<?php

/**
 * Copyrights for You,Me and Us.
 */

class Upload {

	/**
	 * Current targting file
	 *
	 * @var array
	 * @access private
	 */
	private $file;

	/**
	 * The max upload size
	 *
	 * @var integer
	 * @access private
	 */
	private $maxSize = 0;

	/**
	 * List of allow able mime types array
	 *
	 * @var mixed
	 * @access private
	 */
	private $mimeTypes; 

	private $ext;

	private $savePath;

	private $theNewFilename,$uploadedFilename;

	/**
	 * No errors
	 */
	private $currentError = 0;

	/**
	 *
	 */
	private $details = [];


	public $errors = [
        0 => "There is no error.", 
        1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini", 
        2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3 => "The uploaded file was only partially uploaded", 
        4 => "No file was uploaded", 
        6 => "Missing a temporary folder",
        7 => "Failed to write file to disk",
        8 => "A PHP extension stopped the file upload.",
		100 => 'Choose a file to upload',
		101 => 'File Charcte4rs big tahn 225.',
		102 => 'The max size for upload is %s',
		103 => 'Bad extitions',
		104 => 'Bad mime type',
		105 => 'Security error.'
	];

	private function setError ($code) {
		$this->currentError = $code;
	}

	public function getError () {
		return $this->currentError;
	}

	public function getMessage() {
		if (array_key_exists($this->currentError,$this->errors)) {
			return $this->errors[$this->currentError];
		} else {
			return 'No messages';
		}
	}

	/**
	 * @throws RuntimeException
	 */
	public function savePath ($path) {
		try {
			$this->prepareSavePath($path);
		} catch (RuntimeException $e) {
			echo $e->getMessage();
		}
		return $this;
	}

	private function prepareSavePath ($path) {
		if (is_dir($path)) {
			if (is_writable($path) && is_readable($path)) {
				$this->savePath = rtrim($path,'/\\').DIRECTORY_SEPARATOR;
			} else {
				throw new RuntimeException(sprintf("%s must be 0777 0755",$path));
			}
		} else {
			throw new RuntimeException(
				sprintf("Upload directory {%s} not exsits.",$path));
		}
	}

	public function file ($name) {
		$this->file = isset($_FILES[$name]) ? $_FILES[$name] : null;
		return $this;
	}

	public function getFilename () {
		return $this->file['name'];
	}

	public function ext ($ext) {
		$this->ext = $ext;
		return $this;
	}

	public function isExt () {
		if (false !== strpos($this->file['name'],'.')) {
			$ext = $this->getExt();
			if (is_array($this->ext)) {
				return in_array(strtolower($ext),$this->ext);
			} else {
				return strtolower($ext) == $this->ext;
			}
		}
		return false;
	}

	public function mimes ($mime) {
		$this->mimeTypes = $mime;
		return $this;
	}

	public function isMime () {
		if (is_array($this->mimeTypes)) {
			return in_array(strtolower($this->file['type']),$this->mimeTypes);
		} else {
			return strtolower($this->file['type']) == $this->mimeTypes;
		}
	}

	/**
	 *
	 *
	 * 
	 */
	public function size ($size) {
		$this->maxSize = $size;
		return $this;
	}

	public function isFile () {
		return !is_null($this->file['name']) && $this->file['size'] > 0;
	}

	public function isLength () {
		return strlen($this->file['name']) < 225;
	}

	public function isSize () {
		return $this->file['size'] < $this->maxSize;
	}

	public function up () {
		$start = microtime(true); // 0
		if (!$this->isFile()) {
			$this->setError(100);
			return false;
		} else if (!$this->isLength()) {
			$this->setError(101);
			return false;
		} else if (!$this->isSize()) {
			$this->setError(102);
			return false;
		} else if (!$this->isExt()) {
			$this->setError(103);
			return false;
		} else if (!$this->isMime()) {
			$this->setError(104);
			return false;
		} else if ($this->file['error'] != 0) {
			$this->setError($this->file['error']);
			return false;
		} else {

			// @see $this->saveDes();
			$saveDes = $this->savePath.$this->spaceToUnderScore($this->saveDes());
			$uploadedFileName = $this->spaceToUnderScore($this->saveDes());

			if (move_uploaded_file($this->file['tmp_name'],$saveDes)) {
				chmod($saveDes,0644);
				$content = file_get_contents($saveDes);
				if ($this->checkBadCodes($content) == false) {
					unlink($saveDes);
					$this->setError(105);
					return false;
				} else {
					$info = pathinfo($saveDes);
					$end = microtime(true);
					$execute_time = number_format(($end - $start),2);
					$this->details = [
						'filename'      => $info['filename'],
						'basename'      => $uploadedFileName,
						'path'          => $saveDes,
						'extions'       => $this->getExt(),
						'mime_type'     => $this->file['type'],
						'bytes'         => $this->file['size'],
						'readable_size' => $this->convert($this->file['size']),
						'url'           => $this->getUplaodedFileUrl($uploadedFileName),
						'execute_time'  => $execute_time,  
					];
					return true;
				}
			} else {
				return false;
			}
		}
	}

	private function spaceToUnderScore ($filename) {
		return str_replace(' ','_',$filename);
	}

	private function getUplaodedFileUrl ($uploadedFilename) {
		$http = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://'; 
		$dir  = trim(dirname($_SERVER['SCRIPT_NAME']),'\\/');
		$filterDir = empty($dir) ? '/' : '/'.$dir.'/';
		return $http.$_SERVER['HTTP_HOST']
			.$filterDir.basename($this->savePath).'/'
			.$uploadedFilename;
	}

	private function saveDes () {
		$name = is_null($this->theNewFilename) ? $this->file['name'] : $this->theNewFilename;
		if (is_file($this->savePath.$name)) {
			$this->uploadedFilename = time().'_'.$name; 
		} else {
			$this->uploadedFilename = $name;
		}
		return $this->uploadedFilename;
	}

	/**
	 *
	 *
	 *
	 */
	public function getExt ($exttion=true) {
		// filename.php.png.png
		$parts = explode('.',$this->file['name']);
		$ext   = array_pop($parts);
		return $exttion == true  ? $ext : implode('.',$parts);
	}

	/**
	 * MD5, realname, timestamp
	 *
	 *
	 */
	public function rename ($type='realname') {
		$name = $this->getExt(false);
		$ext   = $this->getExt();
		switch ($type) {
			case 'md5':
				$this->theNewFilename = md5($this->file['name']).'.'.$ext;
			break;
			case 'realname':
				$this->theNewFilename = $name.'_'.time().'.'.$ext;
			break;
		}
		return $this;
	}

	public function checkBadCodes ($file) {
		if (preg_match("`<\/?[a-z]+>`i",$file)) {
			return false;
		} else {
			return true;
		}
	}

	public function details () {
		return $this->details;
	}

	/**
	 * To get the memory usage in KB or MB
	 *
	 * @author xelozz -at- gmail.com 
	 * @return string
	 */
	public function convert($size) {
		$unit=array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}

}

?>