<?php
if ( ! defined('PHP_EOL'))
{
	define('PHP_EOL', (DIRECTORY_SEPARATOR == '/') ? "\n" : "\r\n");
} 
if ( ! function_exists('convert_from_gmt'))
{
	function convert_from_gmt($gmt_date,$timezone)
	{

		return mdate("%d/%m/%Y %H:%i",gmt_to_local(strtotime($gmt_date),$timezone,false));
		
	}
	function time_to_mysql($time)
	{
		return date ("Y-m-d H:i:s",$time);
	}
	
}