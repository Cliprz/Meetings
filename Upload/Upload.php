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
			return 'Choose a file.';
		} else if (!$this->isLength()) {
			return 'File charcters big tahn.';
		} else if (!$this->isSize()) {
			return 'The max size for upload is '.$this->maxSize;
		} else if (!$this->isExt()) {
			return 'Bad extitions';
		} else if (!$this->isMime()) {
			return 'Bad mime type';
		} else {

			$saveDes = $this->savePath.time().'_'.$this->file['name'];
			if (move_uploaded_file($this->file['tmp_name'],$saveDes)) {
				chmod($saveDes,0644);
				$content = file_get_contents($saveDes);
				if ($this->checkBadCodes($content) == false) {
					unlink($saveDes);
					return 'Sec ..... alert';
				} else {	
					return 'Uplaoded '.$saveDes;
				}
			} else {
				return 'Uploaded';
			}
		}
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