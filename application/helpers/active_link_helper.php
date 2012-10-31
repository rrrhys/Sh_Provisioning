<?php
if ( ! defined('PHP_EOL'))
{
	define('PHP_EOL', (DIRECTORY_SEPARATOR == '/') ? "\n" : "\r\n");
} 
if ( ! function_exists('is_active'))
{
	function is_active($match_key)
	{

		if(strpos(uri_string(), $match_key)){
			return "class='active'";	
		}
		
	}
	
}