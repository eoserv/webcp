<?php
/* This script is free to modify and distribute

Current version: 1.5

Updated 13th September 2014 (tehsausage@gmail.com) [1.5]
	Default printing behaviour is to escape HTML entities.
	<[~foo]> behaves identially to <[foo]> for backwards compatability.
	<[#foo]> will paste the raw html content of foo.
Updated 23rd April 2009 (tehsausage@gmail.com) [1.4]
	Added elseif
Updated 2nd April 2009 (tehsausage@gmail.com) [1.3]
	Automatic header/footer inclusion
Updated 5th December 2007 (tehsausage@gmail.com) [1.2]
	Added support for htmlentities operator. <[~varname]>
Updated 9th December 2007 (tehsausage@gmail.com) [1.1]
	Fixed error where a file starting with certain tags breaks the parser.
	if and foreach now support arrays
	Added support for logical NOT operator <[if !flag]>
	Added support for an "is_array" operator <[if !@array.key]>
	Added support for an else construct <[if]> x <[else]> y <[endif]>
Created 5th October 2007 (tehsausage@gmail.com) [1.0]
	I hope someone finds this useful.
	Please read syntax.txt for usage information and examples.

TODO!
Clean up array code
Clean up is_array hack

*/

class Template{
	const T_TAG_OPEN = '<[';
	const T_TAG_CLOSE = ']>';
	const T_ARRAY = '.';
	const T_ESCAPE = '\\';
	const T_ESCAPETAG = '!';
	const T_QUOTE = '"';
	const T_SPLIT = ':';
	const T_SPACE = ' ';
	const T_NOT = '!';
	const T_HTMLENTITIES = '~';
	const T_NOESCAPE = '#';
	const T_ISARRAY = '@';
	const T_IF = 'if';
	const T_ELSEIF = 'elseif';
	const T_ELSE = 'else';
	const T_ENDIF = 'endif';
	const T_FOREACH = 'foreach';
	const T_ENDFOREACH = 'endforeach';
	protected $path = './tpl';
	private $vars = array();
	protected $compile_time;
	private $included_header = false;
	private $included_footer = false;
	function __construct($path=null, $autohead = true){
		$this->included_header = $this->included_footer = !$autohead;
		$this->compile_time = 0.0;
		$this->exec_time = 0.0;
		if ($path != null)
			$this->path = $path;
		if (!is_dir($this->path))
			throw new Exception("Template directory does not exist");
		if (!is_dir($this->path.'/compiled'))
			mkdir($this->path.'/compiled');
	}
	static function Secure($data,$string=false){
		if ($string) return addslashes($data);
		if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',$data,$matches))
			return 'error';
		else
			return $matches[0];
	}
	function Compiled($file){
		$php_exists = file_exists($this->path.'/compiled/'.$file.'.php');
		if ($php_exists && file_exists($this->path.'/'.$file.'.htm'))
			return (filemtime($this->path.'/compiled/'.$file.'.php') >= filemtime($this->path.'/'.$file.'.htm'));
		else
			return $php_exists;
	}
	function Exists($file){
		return file_exists($this->path.'/'.$file.'.htm');
	}
	function Execute($file, $maintpl = true){
		if ($maintpl)
		{
			if (!$this->included_header)
			{
				$this->Execute('header', false);
				$this->included_header = true;
			}
		}
		if (!is_null($file))
		{
			if (!is_dir($this->path.DIRECTORY_SEPARATOR.dirname($file).'/compiled'))
				mkdir($this->path.DIRECTORY_SEPARATOR.dirname($file).'/compiled');
			if ($this->Compiled($file))
				$this->Run($file);
			elseif ($this->Exists($file)){
				$this->Compile($file);
				$this->Run($file);
			}
		}
		if ($maintpl)
		{
			if (!$this->included_footer)
			{
				$this->Execute('footer', false);
				$this->included_footer = true;
			}
		}

	}
	function Run($file){
		extract($this->vars,EXTR_SKIP);
		include($this->path.DIRECTORY_SEPARATOR.dirname($file).'/compiled/'.basename($file).'.php');
	}
	function __set($key,$val){
		$this->vars[$key] = $val;
	}
	function __get($key){
		if (isset($this->vars[$key]))
			return $this->vars[$key];
	}
	function __isset($key){
		return isset($this->vars[$key]);
	}
	function __unset($key){
		if (isset($this->vars[$key]))
			unset($this->vars[$key]);
	}
	function Compile($file){
		$data = file_get_contents($this->path.'/'.$file.'.htm');
		$newdata = $data;
		$done = false;
		$state = 0;
		$ptr = 0;
		$tagpos = 0;
		$endtagpos = 0;
		$inside = null;
		$ndoffset = 0;
		$inloop = 0;
		$htmlentities = true;
		while ($done == false){
			switch ($state){
				case 0:
					if (($tagpos = strpos($data,Template::T_TAG_OPEN,$ptr)) === false)
						break 2;
					if (($endtagpos = strpos($data,Template::T_TAG_CLOSE,$tagpos)) === false)
						break 2;
					$ptr = $endtagpos+strlen(Template::T_TAG_CLOSE);
					$inside = substr($data,$tagpos+strlen(Template::T_TAG_OPEN),$endtagpos-$tagpos-strlen(Template::T_TAG_CLOSE));
					$state++;
					break;
				case 1:
					$split_data = str_split($inside);
					$newi = array();
					$quoted = false;
					$escape = false;
					$pt2 = 0;
					$command = false;
					foreach ($split_data as $i){
						if ($escape){
							if (isset($newi[$pt2]))
								$newi[$pt2] .= $i;
							else
								$newi[$pt2] = $i;
							$escape = !$escape;
						} elseif ($i == Template::T_ESCAPE)
							$escape = !$escape;
						elseif ($i == Template::T_QUOTE && !$escape && $pt2 > 0 && !$command)
							$quoted = !$quoted;
						elseif ($i == Template::T_NOESCAPE)
							$htmlentities = false;
						elseif ($i == Template::T_SPLIT)
							$pt2++;
						elseif ($i == Template::T_SPACE && !$quoted){
							$command = true;
							$pt2++;
						} else
							if (isset($newi[$pt2]))
								$newi[$pt2] .= $i;
							else
								$newi[$pt2] = $i;
					}
					$state++;
					break;
				case 2:
					if ($newi[0] == Template::T_IF){
						if ($newi[1][0] == Template::T_NOT) $not = '!'; else $not = '';
						if ($newi[1][0] == Template::T_ISARRAY) $isarray = true; else $isarray = false;
						if (strpos($newi[1],Template::T_ARRAY) === false)
							if ($isarray)
								$code = "<?php if({$not}isset(\$".Template::Secure($newi[1]).") ".($not=='!'?'||':'&&')." {$not}is_array(\$".Template::Secure($newi[1]).")){ ?>";
							else
								$code = "<?php if({$not}isset(\$".Template::Secure($newi[1]).") ".($not=='!'?'||':'&&')." $not\$".Template::Secure($newi[1])."){ ?>";
						else {
							$exa = explode(Template::T_ARRAY,$newi[1]);
							$finalv = Template::Secure(array_shift($exa));
							foreach ($exa as $key){
								if ($key == 'each')
									$finalv = 'each';
								else
									$finalv.="['".Template::Secure($key,true)."']";
							}
							if ($isarray)
								$code = "<?php if({$not}isset(\${$finalv}) ".($not=='!'?'||':'&&')." {$not}is_array(\${$finalv})){ ?>";
							else
								$code = "<?php if({$not}isset(\${$finalv}) ".($not=='!'?'||':'&&')." $not\${$finalv}){ ?>";
						}
					}
					elseif ($newi[0] == Template::T_ELSEIF){
						if ($newi[1][0] == Template::T_NOT) $not = '!'; else $not = '';
						if ($newi[1][0] == Template::T_ISARRAY) $isarray = true; else $isarray = false;
						if (strpos($newi[1],Template::T_ARRAY) === false)
							if ($isarray)
								$code = "<?php } elseif({$not}isset(\$".Template::Secure($newi[1]).") ".($not=='!'?'||':'&&')." {$not}is_array(\$".Template::Secure($newi[1]).")){ ?>";
							else
								$code = "<?php } elseif({$not}isset(\$".Template::Secure($newi[1]).") ".($not=='!'?'||':'&&')." $not\$".Template::Secure($newi[1])."){ ?>";
						else {
							$exa = explode(Template::T_ARRAY,$newi[1]);
							$finalv = Template::Secure(array_shift($exa));
							foreach ($exa as $key){
								if ($key == 'each')
									$finalv = 'each';
								else
									$finalv.="['".Template::Secure($key,true)."']";
							}
							if ($isarray)
								$code = "<?php } elseif({$not}isset(\${$finalv}) ".($not=='!'?'||':'&&')." {$not}is_array(\${$finalv})){ ?>";
							else
								$code = "<?php } elseif({$not}isset(\${$finalv}) ".($not=='!'?'||':'&&')." $not\${$finalv}){ ?>";
						}
					}
					elseif ($newi[0] == Template::T_ESCAPETAG)
						$code = Template::T_TAG_OPEN;
					elseif ($newi[0] == Template::T_ELSE)
						$code = "<?php } else { ?>";
					elseif ($newi[0] == Template::T_ENDIF)
						$code = "<?php } ?>";
					elseif ($newi[0] == Template::T_FOREACH){
						if (strpos($newi[1],Template::T_ARRAY) === false)
							$code = "<?php if (isset(\$".Template::Secure($newi[1]).")){ if (!is_array(\$".Template::Secure($newi[1]).")) \$".Template::Secure($newi[1])." = array(\$".Template::Secure($newi[1])."); foreach(\${$newi[1]} as ".(isset($newi[3])?"\$".Template::Secure($newi[3])."":"\$eachcount")." => ".(isset($newi[2])?"\$".Template::Secure($newi[2])."":"\$each")."){ ?>";
						else {
							$exa = explode(Template::T_ARRAY,$newi[1]);
							$finalv = Template::Secure(array_shift($exa));
							foreach ($exa as $key){
								if ($key == 'each')
									$finalv = 'each';
								else
									$finalv.="['".Template::Secure($key,true)."']";
							}
							$code = "<?php if (isset(\${$finalv})){ if (!is_array(\${$finalv})) \${$finalv} = array(\${$finalv}); foreach(\${$finalv} as ".(isset($newi[3])?"\$".Template::Secure($newi[3])."":"\$eachcount")." => ".(isset($newi[2])?"\$".Template::Secure($newi[2])."":"\$each")."){ ?>";
						}
						$inloop++;
					} elseif ($newi[0] == Template::T_ENDFOREACH){
						$code = "<?php }} ?>";
						$inloop++;
					} else {
						if (strpos($newi[0],Template::T_ARRAY) === false){
							if ($htmlentities)
								$code = "<?php echo htmlspecialchars(isset(\$".Template::Secure($newi[0]).")?\$".Template::Secure($newi[0]).":'".(isset($newi[1])?Template::Secure($newi[1],true):'')."',ENT_QUOTES,'UTF-8') ?>";
							else
								$code = "<?php echo (isset(\$".Template::Secure($newi[0]).")?\$".Template::Secure($newi[0]).":'".(isset($newi[1])?Template::Secure($newi[1],true):'')."') ?>";
							$htmlentities = true;
						} else {
							$exa = explode(Template::T_ARRAY,$newi[0]);
							$finalv = Template::Secure(array_shift($exa));
							foreach ($exa as $key){
								if ($key == 'each')
									$finalv = 'each';
								else
									$finalv.="['".Template::Secure($key,true)."']";
							}
							if ($htmlentities)
								$code = "<?php echo htmlspecialchars(isset(\${$finalv})?\${$finalv}:'".(isset($newi[1])?Template::Secure($newi[1],true):'')."',ENT_QUOTES,'UTF-8') ?>";
							else
								$code = "<?php echo (isset(\${$finalv})?\${$finalv}:'".(isset($newi[1])?Template::Secure($newi[1],true):'')."') ?>";							
							$htmlentities = true;
						}
					}
					$code;
					$olnewdata = strlen($newdata);
					$newdata = substr_replace($newdata,$code,$tagpos-$ndoffset,$endtagpos-$tagpos+strlen(Template::T_TAG_CLOSE));
					$ndoffset += $olnewdata-strlen($newdata);
					$state = 0;
					break;
			}
		}
		file_put_contents($this->path.DIRECTORY_SEPARATOR.dirname($file).'/compiled/'.basename($file).'.php',$newdata);
	}
}




/*

ZOMG A FREE COPY OF...

***[ syntax.txt ]***


Template Syntax (Version 1.1)

ToC

- Template file syntax
- Usage Example

--= Template file syntax

<[variable]>
<[variable:DefaultValue]>
<[variable:"Default Value"]>
<[variable:"Default \"Value\""]>
	If variable exists, print it, otherwise print the
	default value, or nothing if no default is set.

Array access: variable.key{.key{.key{...}}}

<[foreach array {value {key}}]>
	value defaults to 'each'
	key defaults to 'eachcount'
	code after this and before <[endforeach]> is executes
	once for every key in array, value is set to the key
	value, and key is set to the key name.
<[endforeach]>

<[if variable]>
	code after this and before <[endif]> is only executed
	if variable exists, and equals true
<[endif]>

<[!]>
	escape code to print <[

NOTE: Incorrectly named variables will return "error".



--= Usage Example

test.php
	<?php
	$tpl = new Template(); // default path is ./tpl
	$tpl->test_var = "Test";
	$tpl->Execute('test'); // compiles and runs ./tpl/test.htm

./tpl/test.htm
	The value of test_var is <b><[test_var:"Not defined"]></b>

*/
