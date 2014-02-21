<?php

/**
 * Copyrights for You,Me and Us.
 */

class Upload {

	/**
	 * Current targeting file
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
	 * List of allowable mime types
	 *
	 * @var mixed
	 * @access private
	 */
	private $mimeTypes; 

	/**
	 * List of allowable extensions
	 *
	 * @var mixed
	 * @access private
	 */
	private $extensions;

	/**
	 * Upload save path
	 *
	 * @var string
	 * @access private
	 */
	private $savePath;

	/**
	 * The filename after upload
	 *
	 * @var string
	 * @access private
	 */
	private $theNewFilename,$uploadedFilename;

	/**
	 * Current upload error, 0 is default no errors
	 *
	 * @var integer
	 * @access private
	 */
	private $currentError = 0;

	/**
	 * Uploaded file details
	 *
	 * @var array
	 * @access private
	 */
	private $details = array();

	/**
	 * Error messages
	 *
	 * @var array
	 * @access private
	 */
	private $errors = array(
		// PHP Errors
		0   => "There is no error.", 
		1   => "The uploaded file exceeds the upload_max_filesize directive in php.ini", 
		2   => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
		3   => "The uploaded file was only partially uploaded", 
		4   => "No file was uploaded", 
		6   => "Missing a temporary folder",
		7   => "Failed to write file to disk",
		8   => "A PHP extension stopped the file upload.",
		// Object error
		100 => 'Choose a file to upload.',
		101 => 'Filename Characters bigger than 225.',
		102 => 'File size is too big and is not allowed.',
		103 => 'File extension not allowed to upload.',
		104 => 'File Mime type not allowed to upload.',
		105 => 'For security reason the server delete the uploaded file.',
		106 => 'The Server canceled this action for security reasons.'
	);

	/**
	 * Set error
	 *
	 * @param integer Error code
	 * @access private
	 */
	private function setError ($code) {
		$this->currentError = $code;
	}

	/**
	 * Get current error
	 *
	 * @access public
	 * @return integer Error code
	 */
	public function getError () {
		return $this->currentError;
	}

	/**
	 * Get current error message
	 *
	 * @access public
	 * @return string Current error message if was
	 */
	public function getMessage() {
		if (array_key_exists($this->currentError,$this->errors)) {
			return $this->errors[$this->currentError];
		} else {
			return 'No error message to display.';
		}
	}

	/**
	 * Set the upload save path
	 * 
	 * @param string save path
	 * @access public
	 * @throws RuntimeException in failure
	 * @return static
	 */
	public function savePath ($path) {
		try {
			$this->prepareSavePath($path);
		} catch (RuntimeException $e) {
			echo $e->getMessage();
		}
		return $this;
	}

	/**
	 * Preparing upload save path
	 *
	 * @param string save path
	 * @access private
	 */
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

	/**
	 * Set the targeting upload file field
	 *
	 * @param string Input field name
	 * @access public
	 * @return static
	 */
	public function file ($name) {
		$this->file = isset($_FILES[$name]) ? $_FILES[$name] : null;
		return $this;
	}

	/**
	 * Get the upload real filename 
	 *
	 * @access public
	 * @return string real filename
	 */
	public function getFilename () {
		return $this->file['name'];
	}

	/**
	 * Set the allowable extensions
	 *
	 * @param mixed allowable extensions
	 * @access public
	 * @return static
	 */
	public function extensions ($extensions) {
		$this->extensions = $extensions;
		return $this;
	}

	/**
	 * Tells whether if extensions in allowable list
	 *
	 * @access private
	 * @return boolean true if in allowable list, false otherwise
	 */
	private function isExtensions () {
		if (false !== strpos($this->file['name'],'.')) {
			$extensions = $this->getFromFilename('extension');
			if (is_array($this->extensions)) {
				return in_array(strtolower($extensions),$this->extensions);
			} else {
				return strtolower($extensions) == $this->extensions;
			}
		}
		return false;
	}

	/**
	 * Set the allowable mime types
	 *
	 * @param mixed Mime types
	 * @access public
	 * @return static
	 */
	public function mimeTypes ($mimes) {
		$this->mimeTypes = $mimes;
		return $this;
	}

	/**
	 * Tells whether if mime type in allowable list
	 *
	 * @access private
	 * @return boolean true if in allowable list, false otherwise
	 */
	private function isMimeTypes () {
		if (is_array($this->mimeTypes)) {
			return in_array(strtolower($this->file['type']),$this->mimeTypes);
		} else {
			return strtolower($this->file['type']) == $this->mimeTypes;
		}
	}

	/**
	 * Set the max upload file size
	 *
	 * @param integer Max size to upload
	 * @access public
	 * @return static
	 */
	public function size ($size) {
		$this->maxSize = $size;
		return $this;
	}

	/**
	 * Tells whether if there a file to upload
	 *
	 * @access private
	 * @return boolean true if there a file to upload, false otherwise
	 */
	private function isFile () {
		return !is_null($this->file['name']) && $this->file['size'] > 0;
	}

	/**
	 * Check if filename length not big than 255 characters 
	 *
	 * @access private
	 * @return boolean true if lessthan 255 characters, false otherwise
	 */
	private function isLength () {
		return strlen($this->file['name']) < 225;
	}

	/**
	 * Tells whether if file size allowable
	 *
	 * @access private
	 * @return boolean true if file size allowable, false otherwise
	 */
	private function isSize () {
		return $this->file['size'] < $this->maxSize;
	}

	/**
	 * The main method, to upload file
	 *
	 * @access public
	 * @return boolean true if file uploaded, false otherwise
	 */
	public function up () {

		$startTime = microtime(true);

		if ($this->file['error'] != 0) {
			$this->setError($this->file['error']);
			return false;
		} else if (!$this->isHttpHostSafe()
			|| !$this->isMimeTypeSafe()
			|| !$this->isFilenameSafe()) {
			$this->setError(106);
			return false;
		} else if (!$this->isFile()) {
			$this->setError(100);
			return false;
		} else if (!$this->isLength()) {
			$this->setError(101);
			return false;
		} else if (!$this->isSize()) {
			$this->setError(102);
			return false;
		} else if (!$this->isExtensions()) {
			$this->setError(103);
			return false;
		} else if (!$this->isMimeTypes()) {
			$this->setError(104);
			return false;
		} else {

			$saveDestination  = $this->savePath.$this->spaceToUnderscore($this->getUploadedFilename());
			$uploadedFileName = $this->spaceToUnderscore($this->getUploadedFilename());

			if (move_uploaded_file($this->file['tmp_name'],$saveDestination)) {
				chmod($saveDestination,0644);
				$content = file_get_contents($saveDestination);
				if ($this->checkBadCodes($content) == false) {
					unlink($saveDestination);
					$this->setError(105);
					return false;
				} else {
					$info = pathinfo($saveDestination);
					$endTime = microtime(true);
					$execute_time = number_format(($endTime - $startTime),2);
					$this->details = array(
						'filename'      => $info['filename'],
						'basename'      => $uploadedFileName,
						'path'          => $saveDestination,
						'extension'     => $this->getFromFilename('extension'),
						'mime_type'     => $this->file['type'],
						'bytes'         => $this->file['size'],
						'readable_size' => $this->convert($this->file['size']),
						'url'           => $this->getUploadedFileUrl($uploadedFileName),
						'execute_time'  => $execute_time,  
					);
					return true;
				}
			} else {
				return false;
			}
		}
	}

	/**
	 * Convert any space to underscore (_) from filename
	 *
	 * @param string filename
	 * @access private
	 * @return string
	 */
	private function spaceToUnderscore ($filename) {
		return str_replace(' ','_',$filename);
	}

	/**
	 * Get the uploaded file URL
	 *  
	 * @param string uploaded filename
	 * @access private
	 * @return string uploaded file URL
	 */
	private function getUploadedFileUrl ($UploadedFilename) {
		$http = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://'; 
		$directory  = trim(dirname($_SERVER['SCRIPT_NAME']),'\\/');
		$filterDirectory = empty($directory) ? '/' : '/'.$directory.'/';
		return $http.$_SERVER['HTTP_HOST']
			.$filterDirectory.basename($this->savePath).'/'
			.$UploadedFilename;
	}

	/**
	 * Fix XSS attacks from HTTP_HOST headers, (checking)
	 *
	 * @access private
	 * @return boolean true if HTTP_HOST safe, false otherwise
	 */
	private function isHttpHostSafe () {
		if (preg_match('`[a-z0-9_.-]+`i',$_SERVER['HTTP_HOST'])) {
			return true;
		}
		return false;
	}

	/**
	 * Tells whether if mime types header that sent via client is safe
	 *
	 * @access private
	 * @return boolean true if safe, false otherwise
	 */
	private function isMimeTypeSafe () {
		if (preg_match('`[a-z0-9_.-]+\/[a-z0-9_.-]+`i',$this->file['type'])) {
			return true;
		}
		return false;
	}

	/**
	 * Tells whether if filename is safe
	 *
	 * @access private
	 * @return boolean true if safe, false otherwise
	 */
	private function isFilenameSafe () {
		if (preg_match('`(<|>|"|\'|\\|/|:|\*|\?|\|;)`i',$this->file['name'])) {
			return false;
		}
		return true;
	}

	/**
	 * Get the uploaded filename
	 *
	 * @return string the uploaded filename
	 * @access private
	 */
	private function getUploadedFilename () {
		$name = is_null($this->theNewFilename) ? $this->file['name'] : $this->theNewFilename;
		if (is_file($this->savePath.$name)) {
			$this->uploadedFilename = time().'_'.$name; 
		} else {
			$this->uploadedFilename = $name;
		}
		return $this->uploadedFilename;
	}

	/**
	 * This method will get two things from filename
	 * 1.extension, Only extension 
	 * 2.filename,  Without extension
	 *
	 * @param string Needle, what you need
	 * @access private
	 * @return string , or null otherwise
	 */
	private function getFromFilename ($needle='extension') {
		$parts       = explode('.',$this->file['name']);
		$extension   = array_pop($parts);
		if ($needle == 'extension')
			return $extension;
		else if ($needle == 'filename')
			return implode('.',$parts);
		else
			return; // null
	}

	/**
	 * Set the renamed type
	 *
	 * @param string Renamed type
	 * @access public
	 * @return static
	 */
	public function rename ($type='realname') {
		$name = $this->getFromFilename('filename');
		$extension   = $this->getFromFilename('extension');
		switch ($type) {
			case 'md5':
				$this->theNewFilename = md5($this->file['name']).'.'.$extension;
			break;
			case 'realname':
				$this->theNewFilename = $name.'_'.time().'.'.$extension;
			break;
		}
		return $this;
	}

	/**
	 * Check a bad codes from file content
	 *
	 * @param string File content
	 * @access private
	 * @return boolean true if no there a bad codes, false otherwise
	 */
	private function checkBadCodes ($file) {
		// Removed, by Albert (.htaccess handle it)
/*		if (preg_match("`<\/?[a-z]+>`i",$file)) {
			return false;
		} else {
			return true;
		}*/
		return true;
	}

	/**
	 * Get the uploaded file details
	 *
	 * @access public
	 * @return array file details
	 */
	public function details () {
		return $this->details;
	}

	/**
	 * Convert bytes to KB or MB
	 *
	 * @param string bytes size
	 * @author xelozz@gmail.com - Don't ever remove this copyright 
	 * @access public
	 * @return string
	 */
	public function convert($size) {
		$unit = array('B','KB','MB','GB','TB','PB');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}

}

?>