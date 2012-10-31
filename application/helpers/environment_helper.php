<?if ( ! defined('PHP_EOL'))
{
	define('PHP_EOL', (DIRECTORY_SEPARATOR == '/') ? "\n" : "\r\n");
} 
if ( ! function_exists('get_env'))
{
	function get_env()
	{
		$environment = "PROD"; //assume production
		$base_url = strtolower($_SERVER['HTTP_HOST']);
		if($base_url == "provisioning2.devshopous.dev" || $base_url == "provisioning.devshopous.dev"){
			$environment = "DEV";
		}
		else
		{
			$environment = "PROD";
		}
		return $environment;
	}	
}