<?php
/* This script is free to modify and distribute

Current version: 1.3

Updated 24th August 2008 (tehsausage@gmail.com) [1.3]
	Fixed critical exploit - $ and # weren't escaped by Database::Escape()
Updated 17th May 2008 (tehsausage@gmail.com) [1.2]
	Addeed AffectedRows() - returns the number of rows affected by the last query
	Fixed InsertID bug on MySQL (_db instead of db)
Updated 13th March 2008 (tehsausage@gmail.com) [1.1]
	Added InsertID() - returns ID of last INSERT query
Created 20th December 2007 (tehsausage@gmail.com) [1.0]
	PHP port of the db wrapper I wrote in C++

*/

class Database{
	protected $db;
	protected $type;
	protected $count=0;
	protected $debug=array();
	private $sqlite_rowcount;
	function __construct($type,$host=NULL,$user=NULL,$pass=NULL,$name=NULL){
		$this->type = 'unknown';
		switch ($type){
			case 'sqlite':
				try {
				$this->db = new PDO('sqlite:'.$host);
				} catch (PDOException $pdoe) {
					throw new Exception("Could not connect to DB (".$pdoe->getMessage().")");
				}
				$this->type = $type;
				break;
			case 'mysql':
				if (!($this->db = mysql_connect($host,$user,$pass)))
					throw new Exception("Could not connect to DB (".mysql_error().")");
				$this->type = $type;
				if (!mysql_select_db($name,$this->db))
					throw new Exception("Could not select DB");
				break;
			default:
				throw new Exception("Unknown DB type");
		}
	}
	function AffectedRows(){
		switch ($this->type){
			case 'sqlite':
				return $this->sqlite_rowcount;
				break;
			case 'mysql':
				return mysql_affected_rows($this->db);
		}
	}
	function Escape($str){
		switch ($this->type){
			case 'sqlite':
				return str_replace(array("'",'$','#'),array("''",'$$','##'),$str);
				break;
			case 'mysql':
				return mysql_real_escape_string($str,$this->db);
		}
		
	}
	function SQL($query){
		$finalquery = "";
		$i = 1;
		$temp = NULL;
		switch ($this->type){
			case 'sqlite':
				$len = strlen($query);
				for ($ii = 0; $ii < $len; ++$ii){ // todo:optimize
					$letter = $query[$ii];
					if ($letter == '$'){
						if (isset($query[$ii+1]) && $query[$ii+1] == '$')
						{
							$finalquery .= $letter;
							++$ii;
							continue;
						}
						$arg = func_get_arg($i++);
						$finalquery .= str_replace("'","''",$arg);
					} elseif ($letter == '#') {
						if (isset($query[$ii+1]) && $query[$ii+1] == '#')
						{
							$finalquery .= $letter;
							++$ii;
							continue;
						}
						$arg = func_get_arg($i++);
						$finalquery .= (float)$arg;
					} else {
						$finalquery .= $letter;
					}
				}
				break;
			case 'mysql':
				$len = strlen($query);
				for ($ii = 0; $ii < $len; ++$ii){ // todo:optimize
					$letter = $query[$ii];
					if ($letter == '$'){
						if (isset($query[$ii+1]) && $query[$ii+1] == '$')
						{
							$finalquery .= $letter;
							++$ii;
							continue;
						}
						$arg = func_get_arg($i++);
						$finalquery .= mysql_real_escape_string($arg,$this->db);
					} elseif ($letter == '#') {
						if (isset($query[$ii+1]) && $query[$ii+1] == '#')
						{
							$finalquery .= $letter;
							++$ii;
							continue;
						}
						$arg = func_get_arg($i++);
						$finalquery .= (float)$arg;
					} else {
						$finalquery .= $letter;
					}
				}
			}
		return $this->RawSQL($finalquery);
	}
	function RawSQL($finalquery){
		$this->count++;
		$start_query = microtime(true);
		switch ($this->type){
			case 'sqlite':
				$ret = array();
				if (!($query = $this->db->prepare($finalquery)))
					throw new Exception("Query failed. (".implode(' ',$this->db->errorInfo()).")");
				if ($query->execute()){
					$all = $query->fetchAll(PDO::FETCH_ASSOC);
					if (!empty($all))
						foreach ($all as $a)
							$ret[] = $a;
					$this->sqlite_rowcount = $query->rowCount();
				} else
					throw new Exception("Query failed. (".implode(' ',$query->errorInfo()).")");
				$end_query = microtime(true);
				$this->debug[] = array($finalquery,($end_query-$start_query)*1000);
				return $ret;
			case 'mysql':
				$ret = array();
				$result = mysql_query($finalquery,$this->db);
				if ($result){
					while (($a = @mysql_fetch_assoc($result)) !== false)
						$ret[] = $a;
				} else
					throw new Exception("Query failed. (".mysql_error($this->db).")");
				$end_query = microtime(true);
				$this->debug[] = array($finalquery,($end_query-$start_query)*1000);
				return $ret;
		}
	}
	function InsertID(){
		switch ($this->type){
			case 'sqlite':
				return $this->db->lastInsertID();
			case 'mysql':
				return @mysql_insert_id($this->db);
		}
	}
	function Count(){
		return $this->count;
	}
	function Debug(){
		return $this->debug;
	}
}
