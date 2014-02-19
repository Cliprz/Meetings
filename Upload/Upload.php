<?php

class Upload {

	private $file;

	/**
	 *
	 *
	 */
	private $maxSize = 0;

	/**
	 * @var mixed
	 */
	private $mimeTypes; 

	private $ext;

	private $savePath;

	private $theNewFilename;

	/**
	 * No errors
	 */
	private $currentError = 0;


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
			$parts = explode('.',$this->file['name']);
			$ext   = array_pop($parts);
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
			$saveDes = $this->saveDes();

			if (move_uploaded_file($this->file['tmp_name'],$saveDes)) {
				chmod($saveDes,0644);
				$content = file_get_contents($saveDes);
				if ($this->checkBadCodes($content) == false) {
					unlink($saveDes);
					$this->setError(105);
					return false;
				} else {	
					return true;
				}
			} else {
				return false;
			}
		}
	}

	private function saveDes () {
		$name = is_null($this->theNewFilename) ? $this->file['name'] : $this->theNewFilename;
		if (is_file($this->savePath.$name)) {
			return $this->savePath.time().'_'.$name; 
		} else {
			return $this->savePath.$name;
		}
	}

	/**
	 * MD5, realname, timestamp
	 *
	 *
	 */
	public function rename ($type='realname') {
		$parts = explode('.',$this->file['name']);
		$ext   = array_pop($parts);
		switch ($type) {
			case 'md5':
				$this->theNewFilename = md5($this->file['name']).'.'.$ext;
			break;
			case 'realname':
				$this->theNewFilename = implode('.',$parts).'_'.time().'.'.$ext;
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

}

?>