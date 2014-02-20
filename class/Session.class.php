<?php
/* This script is free to modify and distribute

Current version: 1.0

Created 14th July 2007 (tehsausage@gmail.com) [1.0]
	Abstract session storage class

*/

class Session{
	protected $prefix = '';
	protected $start = 0;
	function __construct($prefix=''){
		$this->prefix = $prefix;
		$this->start = microtime(true);
		session_start();
	}
	function __get($key){
		$key .= $this->prefix;
		if (isset($_SESSION[$key]))
			return $_SESSION[$key];
	}
	function __set($key,$value){
		$key .= $this->prefix;
		if (isset($_SESSION[$key]) && $_SESSION[$key] == $value) return;
		$_SESSION[$key] = $value;
	}
	function __isset($key){
		$key .= $this->prefix;
		return isset($_SESSION[$key]);
	}
	function __unset($key){
		$key .= $this->prefix;
		if (!isset($_SESSION[$key])) return;
		unset($_SESSION[$key]);
	}
	function __destruct(){
		session_write_close();
	}
	function Start(){
		return $this->start;
	}
}
