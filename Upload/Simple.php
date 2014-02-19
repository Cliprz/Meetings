<?php

class Simple { 

	public static function getInstance() {
		static $instance;
		if (!$instance instanceof Simple) {
			$instance = new self();	
		}
		return $instance;
	}

	private function __construct () {}	
	private function __clone () {}	
	private function __wakeup () {}	


	public function printMe () {
		echo "Hello am in ".__CLASS__;
	}

}

$a = Simple::getInstance();
$b = Simple::getInstance();

var_dump($a,$b);

?>