<?php

/**
 * Simple string encoding - offer limited security to protect sensitive data from being viewed
 * @var string $string the string to encode
 * @var string $key the encoding key
 * @return string the encoded string
 */
function encode($string, $key)
{
	$key = sha1($key);
	$strLen = strlen($string);
	$keyLen = strlen($key);
	$j = 0;
	$hash = '';
	
	for ($i = 0; $i < $strLen; ++$i)
	{
		$ordStr = ord(substr($string,$i,1));
		if ($j == $keyLen)
		{
			$j = 0;
		}
		$ordKey = ord(substr($key, $j, 1));
		$j++;
		$hash .= strrev(base_convert(dechex($ordStr+$ordKey), 16, 36));
	}
	
	return $hash;
}

/**
 * Simple string decoding - offer limited security to protect sensitive data from being viewed
 * @var string $string the string to decode
 * @var string $key the encoding key
 * @return string the decoded string
 */
function decode($string, $key)
{
	$key = sha1($key);
	$strLen = strlen($string);
	$keyLen = strlen($key);
	$j = 0;
	$hash = '';
	
	for ($i = 0; $i < $strLen; $i += 2)
	{
		$ordStr = hexdec(base_convert(strrev(substr($string, $i, 2)), 36, 16));
		if ($j == $keyLen)
		{
			$j = 0;
		}
		$ordKey = ord(substr($key, $j, 1));
		$j++;
		$hash .= chr($ordStr-$ordKey);
	}
	
	return $hash;
}

/**
 * Builds the context vars list for log and/or report
 * @var array $vars an array of vars to output
 * @var int $level the level in the context vars (prevent infinite recursion)
 * @return string the output to display
 */
function buildSimpleVarList($vars, $level = 0)
{
	$output = '';
	$indent = str_repeat('	', $level*2);
	$prefix = ($level == 0) ? '$' : '\'';
	$suffix = ($level == 0) ? '' : '\'';
	
	foreach ($vars as $key => $value)
	{
		if ($level == 0)
		{
			$key = $prefix.$key;
		}
		elseif (!is_numeric($key))
		{
			$key = $prefix.$key.$suffix;
		}
		
		if (is_array($value))
		{
			$length = count($value);
			
			$output .= '<li>'."\n".$indent;
			$output .= '	<b>'.$key.':</b> array('.$length.')'."\n".$indent;
			$output .= '	<ul>'."\n".$indent;
			
			if ($level > 5)
			{
				// Prevent infinite recursion
				$output .= '		<li><em>(too much levels)</em></li>'."\n".$indent;
			}
			elseif ($length > 0)
			{
				$output .= '		'.buildSimpleVarList($value, $level+1)."\n".$indent;
			}
			else
			{
				$output .= '	<li><em>(empty)</em></li>'."\n".$indent;
			}
			
			$output .= '	</ul>'."\n".$indent;
			$output .= '</li>'."\n".$indent;
		}
		else
		{
			$output .= '<li class="force-wrap"><b>'.$key.':</b> '.describeVar($value).'</li>'."\n".$indent;
		}
	}
	
	return rtrim($output);
}

/**
 * Builds the context vars list for display
 * @var array $vars an array of vars to output
 * @var int $level the level in the context vars (prevent infinite recursion)
 * @return string the output to display
 */
function buildVarList($vars, $level = 0)
{
	$output = '';
	$indent = str_repeat('	', 6+($level*2));
	$prefix = ($level == 0) ? '$' : '\'';
	$suffix = ($level == 0) ? '' : '\'';
	
	foreach ($vars as $key => $value)
	{
		if ($level == 0)
		{
			$key = $prefix.$key;
		}
		elseif (!is_numeric($key))
		{
			$key = $prefix.$key.$suffix;
		}
		
		if (is_array($value))
		{
			$length = count($value);
			
			$output .= '<li class="close">'."\n".$indent;
			$output .= '	<b class="toggle"></b>'."\n".$indent;
			$output .= '	<span><b>'.$key.':</b> array('.$length.')</span>'."\n".$indent;
			$output .= '	<ul>'."\n".$indent;
			
			if ($level > 5)
			{
				// Prevent infinite recursion
				$output .= '		<li><span><em>(too much levels)</em></span></li>'."\n".$indent;
			}
			elseif ($length > 0)
			{
				$output .= '		'.buildVarList($value, $level+1)."\n".$indent;
			}
			else
			{
				$output .= '	<li><span><em>(empty)</em></span></li>'."\n".$indent;
			}
			
			$output .= '	</ul>'."\n".$indent;
			$output .= '</li>'."\n".$indent;
		}
		else
		{
			$output .= '<li class="force-wrap"><span><b>'.$key.':</b> '.describeVar($value).'</span></li>'."\n".$indent;
		}
	}
	
	return rtrim($output);
}

/**
 * Return a description string of the var
 * @var mixed $var the var
 * @return string the description
 */
function describeVar($var)
{
	if (is_array($var))
	{
		return 'array('.count($var).')';
	}
	elseif (is_object($var))
	{
		$id = method_exists($var, 'getId') ? $var->getId() : (property_exists($var, 'id') ? $var->id : '');
		return get_class($var).'('.$id.')';
	}
	elseif (is_bool($var))
	{
		return ($var ? 'true' : 'false');
	}
	elseif (is_string($var))
	{
		return '\''.$var.'\'';
	}
	else
	{
		return $var;
	}
}

/**
 * Trim file path to hide document root
 * @var string $file the file path
 * @var int $maxLength the max number of chars, or 0 for no limit (optional)
 * @return string the path without the document root
 */
function trimDocumentRoot($file, $maxLength = 0)
{
	// Windows systems
	$file = str_replace('\\', '/', $file);
	
	if (isset($_SERVER['DOCUMENT_ROOT']))
	{
		if (strpos($file, $_SERVER['DOCUMENT_ROOT']) === 0)
		{
			$file = substr($file, strlen($_SERVER['DOCUMENT_ROOT']));
		}
	}
	
	if ($maxLength > 0)
	{
		$file = shortenFilePath($file, $maxLength);
	}
	
	return $file;
}

/**
 * Shorten file name to requested size
 * @param string $file the file path
 * @var int $maxLength the max number of chars
 * @return string the shortened path
 */
function shortenFilePath($file, $maxLength)
{
	if (strlen($file) > $maxLength)
	{
		$slashPos = strpos($file, '/', strlen($file)-$maxLength);
		if ($slashPos !== false)
		{
			$file = '...'.substr($file, $slashPos);
		}
		else
		{
			$file = '...'.substr($file, strlen($file)-$maxLength);
		}
	}
	
	return $file;
}

/**
 * Fallback if json_encode is not defined
 * Original code from multiple contributors on json_encode's manual page :
 * @url http://fr.php.net/manual/en/function.json-encode.php
 */
if (!function_exists('json_encode'))
{
	function json_encode($a)
	{
		// Generic types
		if (is_null($a))
		{
			return 'null';
		}
		if ($a === false)
		{
			return 'false';
		}
		if ($a === true)
		{
			return 'true';
		}
		
		if (is_scalar($a))
		{
			if (is_float($a))
			{
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}
			
			if (is_string($a))
			{
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"'.str_replace($jsonReplaces[0], $jsonReplaces[1], $a).'"';
			}
			else
			{
				return $a;
			}
		}
		
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		{
			if (key($a) !== $i)
			{
				$isList = false;
				break;
			}
		}
		
		$result = array();
		if ($isList)
		{
			foreach ($a as $v)
			{
				$result[] = json_encode($v);
			}
			return '['.join(',', $result).']';
		}
		else
		{
			foreach ($a as $k => $v)
			{
				$result[] = json_encode($k).':'.json_encode($v);
			}
			return '{'.join(',', $result).'}';
		}
	}
}