<?if ( ! defined('PHP_EOL'))
{
	define('PHP_EOL', (DIRECTORY_SEPARATOR == '/') ? "\n" : "\r\n");
} 
if ( ! function_exists('curl_get'))
{
	function curl_get($url)
	{
		
		$server_name = strtolower($_SERVER['HTTP_HOST']);
		$crl = curl_init();
		if(strpos($server_name,"devshopous.dev") > -1){
		curl_setopt($crl, CURLOPT_PROXY, '127.0.0.1:8888'); 
		}
			curl_setopt($crl, CURLOPT_URL, $url);
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 5); // 5 seconds
			$content = curl_exec($crl);
			return $content;
	}	
	function clean_to_ascii($text){
    $text = preg_replace("/[^A-Za-z0-9 ]/", '', $text);
    return $text;
	}
	
	function curl_get_and_save($url,$save_to)
	{
		
		$server_name = strtolower($_SERVER['HTTP_HOST']);
		$clean_url = clean_to_ascii($url);
		$crl = curl_init();
		if(strpos($server_name,"devshopous.dev") > -1){
		curl_setopt($crl, CURLOPT_PROXY, '127.0.0.1:8888'); 
		}
			curl_setopt($crl, CURLOPT_URL, $url);
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 5); // 5 seconds
			
			// grab URL and pass it to the browser
			$out = curl_exec($crl);

			curl_close($crl);
			echo $clean_url;
			$fp = fopen($save_to, 'w');
			fwrite($fp, $out);
			fclose($fp);
			return true;
		}
}
