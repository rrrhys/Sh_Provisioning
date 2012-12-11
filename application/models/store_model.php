<?

class Store_model extends CI_Model
{
	public $sites_base = "";
	public $asset_base = "";
	public $instances_location = "";
	public $base_url = "";
	public $server_name = "";
	public $dev_base = "devshopous.dev";
	public $prod_base = "shopous.com.au";
	public $master_db_pass = "";
	public $output_to_browser = false;
	public $master_db_connection = array();
	public $salt = "708324ee-c68e-4f73-a790-4db3e97cfb6d";
	public $analytics_db_name = "";
	public $analytics_db_prefix = "";
	public $errors = array();
	public function __construct(){
		parent::__construct();
		$this->base_url = strtolower($_SERVER['HTTP_HOST']);
		
	//	if($this->base_url == "provisioning2.devshopous.dev" || $this->base_url == "provisioning.devshopous.dev"){
		if(get_env() == "DEV"){
		$this->server_name = $this->dev_base;
		$this->master_db_pass = "insecure_pass";
		$this->sites_base = "../sites/";
		$this->asset_base = "../sites/assets/";
		$this->instances_location = "../sites/instances.php";
		$this->analytics_db_name = "piwik";
		$this->analytics_db_prefix = "piwik_";
		$this->analytics_auth_token = "e0ab1c86cc5181b9db6924159a19ac82";
		}
		elseif(get_env() == "PROD")
		{
		$this->server_name = $this->prod_base;
		$this->master_db_pass = "kaWraBReTufr22af";
		$this->sites_base = "../../stores/shopous-sites/";
		$this->asset_base = $this->sites_base . "assets/";
		$this->instances_location = $this->sites_base . "instances.php";
		$this->analytics_db_name = "analytics";
		$this->analytics_db_prefix = "analytics_";
		$this->analytics_auth_token = "81d12cde78bed76ace73036bc59710aa";
		}
		else
		{
			echo "Environment not matched";
			die();
		}
		$master_db_connection = &$this->master_db_connection;
		$master_db_connection['hostname'] = "127.0.0.1";
		$master_db_connection['username'] = "root";
		$master_db_connection['password'] = $this->master_db_pass;
		$master_db_connection['database'] = "shopous_central";
		$master_db_connection['dbdriver'] = "mysql";
		$master_db_connection['dbprefix'] = "";
		$master_db_connection['pconnect'] = TRUE;
		$master_db_connection['db_debug'] = TRUE;
		$master_db_connection['cache_on'] = FALSE;
		$master_db_connection['cachedir'] = "";
		$master_db_connection['char_set'] = "utf8";
		$master_db_connection['dbcollat'] = "utf8_general_ci";
		$this->load->database($this->master_db_connection);
	}
	public function add_store_to_index($username,$store_url,$store_name,$version,$shopkeeper_token,$shopous_token,$db_details){
		$insert = array(
				'id'=>uniqid(),
				'user_id'=>$username,
				'store_url'=>$store_url,
				'store_name'=>$store_name,
				'version'=>$version,
				'mysql_user_name'=>$username,
				'shopkeeper_token'=>$shopkeeper_token,
				'shopous_token'=>$shopous_token,
				'db_name'=>$db_details['database'],
				'db_details'=>json_encode($db_details)
			);
		$this->db->insert('stores',$insert);
		return $this->db->affected_rows();
	}
	public function create_store($version="",$product_url,$store_name,$email_address){
		$retval = array('result'=>'fail','steps_completed'=>array(),'messages'=>array());
		if($version == ""){
			$site_version = $this->get_latest_release();
		}else{
			$site_version = $this->get_release($version);
		}
		$version = $site_version['version'];
			$db_details = array(
				'database'=>$this->store_model->_make_database_name(),
				'username'=>$this->store_model->_make_database_username(),
				'password'=>uniqid() . "shopoussalt20111208");
			
			$user_password = uniqid();
			$analytics_password = uniqid();
			
			$analytics_site_id = 0;
			
			$shopkeeper_token = gen_uuid();
			$shopous_token = gen_uuid();


		if(!$site_version){
			$retval['messages'][] = "Could not retrieve site version.";
			return $retval;
		}
		else{$retval['steps_completed'][] = "Get site version";}
		if($this->instance_url_taken($product_url)){
			$retval['messages'][] = "URL Taken: That website address is already taken!";
			return $retval;
		}
		$folder_locations = $this->create_folders($version,$product_url);

		$analytics_password = uniqid();
		$analytics_site = $this->setup_analytics($db_details['username'],
												$analytics_password,
												$email_address,
												$email_address,
												$this->analytics_auth_token,
												$product_url
												);
		if(!isset($analytics_site['analytics_site_id'])){
			$retval['messages'][] = "Analytics setup failed: " . $analytics_site['errors'];
			return retval;
		}
		else{$retval['steps_completed'][] = "Set up analytics";}

		if(!$folder_locations){
			$retval['messages'][] = "Could not create folders.";
			return $retval;
		}
		else{$retval['steps_completed'][] = "Create folders";}
		
		$config_locations = $this->copy_config_files($site_version,$folder_locations);
		if(!$config_locations){
			$retval['messages'][] = "Could not copy config files.";
			$retval['messages'] = array_merge($retval['messages'], $this->errors);
			return $retval;			
		}
		else{$retval['steps_completed'][] = "Copy Config File";}
		if(!$this->setup_config_file($config_locations['config_file_location'])){
			$retval['messages'][] = "Could not write to config file.";
			$retval['messages'] = array_merge($retval['messages'], $this->errors);
			return $retval;			
		}
		else{$retval['steps_completed'][] = "Modify Config File";}
		if(!$this->setup_db_file($config_locations['database_file_location'],$db_details)){
			$retval['messages'][] = "Could not write to DB file.";
			return $retval;			
		}
		else{$retval['steps_completed'][] = "Write DB Setup";}
		
		if(!$this->setup_user_database($db_details,$version)){
			$retval['messages'][] = "Could not set up user database.";
			$retval['messages'] = array_merge($retval['messages'], $this->errors);
			return $retval;
		}
		else{$retval['steps_completed'][] = "Exec DB Setup";}
		
		if(!$this->add_store_to_index($db_details['username'],$product_url,$store_name,$version,$shopkeeper_token,$shopous_token,$db_details)){
				$retval['messages'][] = "Could not add store to index.";
				return $retval;	
		}else{$retval['steps_completed'][] = "Add store to index";}
		
		if(!$this->regenerate_instances_file()){
			$retval['messages'][] = "Could not remake routing page.";
			return $retval;			
		}else{$retval['steps_completed'][] = "Remake Routing page";}
		if(!$this->populate_user_db($analytics_site,$db_details,$email_address,$user_password,$shopous_token,$shopkeeper_token,$store_name)){
			$retval['messages'][] = "Could not add user specific data to their DB";
			return $retval;
		}else{$retval['steps_completed'][] = "Add user specific details to their DB";}


		$retval['result'] = 'success';
		$retval['store_url'] = $product_url;
		$retval['shopous_token'] = $shopous_token;
		$retval['shopkeeper_token'] = $shopkeeper_token;
		$retval['username'] = $email_address;
		$retval['user_password'] = $user_password;
		return $retval;
	}
	function populate_user_db($analytics_site,$db_details,$email_address,$password,$shopous_token,$shopkeeper_token,$store_name){
		$client_db_connection = &$this->master_db_connection;
		$client_db_connection['hostname'] = "127.0.0.1";
		$client_db_connection['username'] = $db_details['username'];
		$client_db_connection['password'] = $db_details['password'];
		$client_db_connection['database'] = $db_details['database'];
		$client_db = $this->load->database($client_db_connection,TRUE);	
		$insert = array('id'=>				gen_uuid(),
						'email_address'=>	$email_address,
						'password'=>		sha1($password . $this->salt),
						'timezone'=>		'UTC',
						'active'=>			1,
						'auth_level'=>		2,
						'auth_token'=>		$shopkeeper_token);
		$client_db->insert('users',$insert);	
		
		
		$insert = array('id'=>				gen_uuid(),
						'email_address'=>	'rhys.williams@shopous.com',
						'password'=>		sha1($password . $this->salt),
						'timezone'=>		'UTC',
						'active'=>			1,
						'auth_level'=>		3,
						'auth_token'=>		$shopous_token);
		//create impersonation user too.
		$client_db->insert('users',$insert);
			$analytics_url = "http://analytics.".$this->server_name;
				
		//change some pre-loaded configurables.
			$client_db->where('name','store_name')->set('description',$store_name)->update('configurables');
			$client_db->where('name','analytics_site_id')->set('description',$analytics_site['analytics_site_id'])->update('configurables');
			$client_db->where('name','analytics_password')->set('description',md5($analytics_site['password']))->update('configurables');
			$client_db->where('name','analytics_username')->set('description',$db_details['username'])->update('configurables');
			$client_db->where('name','analytics_url')->set('description',$analytics_url)->update('configurables');
			$client_db->where('name','analytics_token_auth')->set('description',$analytics_site['analytics_token_auth'])->update('configurables');
		return true;

	}
	function delete_from_db($instance_id){
		$retval = array('result'=>'success','errors'=>'');
		$instance = $this->get_instance($instance_id);
		if($instance){
			$this->db->where('id',$instance_id);
			$this->db->delete('stores');

			$this->load->dbforge();
			$result1 = $this->dbforge->drop_database($instance['db_name']);
			$result2 = $this->db->simple_query("delete from mysql.user where user = '" . $instance['mysql_user_name'] . "';flush privileges;");
			if(!$result1){
				$retval['errors'] = "Could not drop database " . $instance['db_name'];
			}
			if(!$result2){
				$retval['errors'] = "Could not delete mysql user " . $instance['mysql_user_name'];
			}
		}
		else
		{
			$retval['result'] = 'fail';
			$retval['errors'] = "Instance didn't exist in DB.";
		}
		return $retval;
	}//store_model->delete_from_db($store_id)
	function delete_from_filesystem($instance_id){
		$retval = array('result'=>'fail','errors'=>'');
		$instance = $this->get_instance($instance_id);
		if($instance){
			$result = $this->delete_folders($instance['version'],$instance['store_url']);
			if(!$result){
				$retval['result'] = 'fail';
				$retval['errors'] = "Couldn't delete folder. Permissions problem?";
			}
			$retval['result'] = "success";
		}
		else
		{
			$retval['result'] = 'fail';
			$retval['errors'] = "Instance did not exist";
		}
		return $retval;
	}	
	function delete_from_analytics_by_idsite($idsite){
		$retval = array('result'=>'fail','errors'=>'');
		//echo json_encode($instance);
		//get piwik site id.
		$this->db->where('idsite',$idsite);
		//echo $instance['store_url'];
		$analytics_site = $this->db->get($this->analytics_db_name.".".$this->analytics_db_prefix."site")->row_array();

		//echo json_encode($analytics_site);
		if(!isset($analytics_site['idsite'])){
			$retval['errors'] = "Site did not exist in analytics.";
			return $retval;
		}
		$this->db->where('idsite',$analytics_site['idsite']);
		$analytics_user = $this->db->get($this->analytics_db_name.".".$this->analytics_db_prefix."access")->row_array();
		
		//	echo json_encode($analytics_user);
		
		$this->db->where('idsite',$analytics_site['idsite']);
		$this->db->delete($this->analytics_db_name.".".$this->analytics_db_prefix."site");
		$this->db->where('idsite',$analytics_site['idsite']);
		$this->db->delete($this->analytics_db_name.".".$this->analytics_db_prefix."access");
		if(isset($analytics_user['login'])){
			$this->db->where('login',$analytics_user['login']);
			$this->db->delete($this->analytics_db_name.".".$this->analytics_db_prefix."user");
		}
		$retval['result'] = 'success';
		return $retval;
	}
	function delete_analytics_user($login){
		$retval = array('result'=>'fail','errors'=>'');
		$this->db->where('login',$login);
		$this->db->delete($this->analytics_db_name.".".$this->analytics_db_prefix."user");
		$retval['result'] = 'success';
		return $retval;
	}
	function delete_from_analytics($instance_id){
		$retval = array('result'=>'fail','errors'=>'');
		$instance = $this->get_instance($instance_id);
		//echo json_encode($instance);
		//get piwik site id.
		$this->db->where('name',$instance['store_url']);
		$analytics_site = $this->db->get($this->analytics_db_name.".".$this->analytics_db_prefix."site")->row_array();
		$retval = $this->delete_from_analytics_by_idsite($analytics_site['idsite']);
		return $retval;
	}
	function list_instances_active($strip_sensitive = true){
		$this->db->where('enabled',1);
		$q = $this->db->get('stores')->result_array();
		if($strip_sensitive){
			$sensitive = array('version','user_id','last_changed','error_logging','mysql_user_name','enabled','shopous_token','shopkeeper_token','db_name','db_details');
			foreach($q as &$r){
				foreach($r as $k=>$v){
					if(in_array($k, $sensitive)){
						//echo $k;
						unset($r[$k]);
					}
				}
			}
		}
		return $q;
	}
	function list_analytics(){
		$analytics_sites = $this->db->get($this->analytics_db_name.".".$this->analytics_db_prefix."site")->result_array();
		$analytics_users = $this->db->get($this->analytics_db_name.".".$this->analytics_db_prefix."user")->result_array();
		return array(	'sites'=>$analytics_sites,
						'users'=>$analytics_users);
	}
	function get_thumbnail_url($store_url){
		//http://api1.thumbalizr.com/?url=http://www.ford.de&width=250
		$api_address = "http://img.bitpixels.com/getthumbnail?code=42769&size=200&url=".$store_url."";
/*
		$this->load->helper("curl_helper");

		$stored_name = "thumbnails/" . clean_to_ascii($store_url);

		if(file_exists($stored_name)){
			return $stored_name;
		}else{
			curl_get_and_save($api_address,$stored_name);
			return $stored_name;
		}*/
		return $api_address;
	}
	function get_instance($instance_id){
		$this->db->where('id',$instance_id);
		$store = $this->db->get('stores')->row_array();
		if($store){
		$this->db->where('main_url',$store['store_url']);
		$analytics_store = $this->db->get($this->analytics_db_name.".".$this->analytics_db_prefix."site")->row_array();
		$store['analytics'] = $analytics_store;
		
		return $store;
		}
		else
		{
			return false;
		}
	}
	function regenerate_instances_file(){

		//echo "Regenerate Instances";
		$all_stores = $this->list_instances_active(false);
		//	echo json_encode($all_stores);


		$instances_location = $this->instances_location;

		$file_handle = fopen($instances_location,'r');
		if(!$file_handle){
			return false;
		}
		$contents = fread($file_handle,filesize($instances_location));
		if(!$contents){
			return false;
		}
		$rows = explode("\n",$contents);
		//remove all previously autogenerated code.
		$rows_out = array();
		$trigger_found = false;
		for($i = 0; $i < count($rows);$i++){
			//echo $rows[$i] . "\n";
			if($rows[$i] == "//shopous_autogenerated"){
			$trigger_found = true;
			$rows_out[] = $rows[$i];

			//drop in auto gen.
			foreach($all_stores as $store){
				$store['store_url'] = str_ireplace ("http://", "", $store['store_url']);
			//$rows_out[] = $content;
			$rows_out[] = "if(ENVIRONMENT == '{$store['store_url']}'){\$application_folder = '{$store['version']}';error_reporting({$store['error_logging']});};";
			}
				while($rows[$i] != "//end_shopous_autogenerated"){
				$i++;
				}

			$rows_out[] = $rows[$i];
			}
			else
			{
			$rows_out[] = $rows[$i];
			}
		}
		if(!$trigger_found){
			echo "Trigger not found.";
			return false;

		}
		fclose($file_handle);
		$file_handle = fopen($instances_location,'w');
		foreach($rows_out as $row_out){
		fwrite($file_handle,$row_out . "\n",strlen($row_out) + 1);
		}
		fclose($file_handle);
		return true;
	}

	public function create_folders($version,$product_url){
		$folder_name = $product_url;
		$folder_name = str_replace("http://","",$folder_name);
		//$folder_name = str_replace("/","",$folder_name);
		//$folder_name = str_replace(":","",$folder_name);
		//config
		$config_base = $this->sites_base .  $version . "/config/";
		$site_base = $config_base . $folder_name;
		$asset_base = $this->asset_base . $folder_name;
		@mkdir($config_base,0755);
		//site
		@mkdir($site_base);
		//assets
		@mkdir($asset_base,0755);
		//echo json_encode(error_get_last());
		return array('config_base'=>$config_base,
					'site_base'=>$site_base,
					'asset_base'=>$asset_base);
	}
	public function copy_config_files($site_version,$folder_locations){
		$config_file_location = $folder_locations['site_base'] . "/config.php";
		$database_file_location = $folder_locations['site_base'] . "/database.php";
		if(!is_writable($site_version['config_file']) && !is_writable($site_version['database_config_file'])){
			$this->errors[] = "Config and database file are not writable (e.g. " . $config_file_location . ")";
			return false;
		}
		$this->debug_message("copying config ".$site_version['config_file'] . " to ".$config_file_location);
		$this->debug_message("copying config ".$site_version['database_config_file']." to ".$database_file_location);
		copy($site_version['config_file'],$config_file_location);
		copy($site_version['database_config_file'],$database_file_location);
		return array(	'config_file_location'=>$config_file_location,
						'database_file_location'=>$database_file_location);
	}
	public function delete_folders($version,$product_url){
		$folder_name = $product_url;
		$folder_name = str_replace("http://","",$folder_name);
		//config
		$config_base = $this->sites_base . $version . "/config/";
		$site_base = $config_base . $folder_name;
		//site
		rrmdir($site_base);
		//assets
		rrmdir($this->asset_base . $folder_name,0755);
		return true;		
	}
	public function get_latest_release(){
		$latest_release_name = "latest_release.txt";
		$full_file = $string = read_file($latest_release_name);

		$rows = explode("\n",$full_file);

		foreach($rows as &$row){
		$row = explode("|",$row);
		}

		$latest_release = array();
		for($i=0;$i<count($rows[0]);$i++){
		$latest_release[$rows[0][$i]] = $rows[1][$i];
		}

		//echo json_encode($latest_release);
		return $latest_release;
	}
	function get_release($version_id){
		$releases = $this->get_releases();
		foreach($releases as $release){
			if($release['version'] == $version_id){
				return $release;
			}
		}
		return false;
	}
	function get_releases(){
		$release_name = "releases_available.txt";
		$full_file = read_file($release_name);
		$rows = explode("\n",$full_file);
		$releases = array();
		for($i=0;$i<count($rows);$i++){
		$rows[$i] = explode("|",$rows[$i]);
			$release = array();
			//foreach row in the set, make a new array element with the key = col header, val = existing val.
			for($j=0;$j<count($rows[$i]);$j++){
			$release[$rows[0][$j]] = $rows[$i][$j];
			}
		$releases[] = $release;		
		}
		unset($releases[0]);

		//echo json_encode($releases);
		return $releases;
	}
	function get_upgrade_path($version_id,$upgrade_from = ""){
		$upgrade_path = array();
		$release = $this->get_release($version_id);
		$upgrade_path[] = $release;
		while($release['upgrade_from_version'] != $upgrade_from){
			$release = $this->get_release($release['upgrade_from_version']);
			$upgrade_path[] = $release;

		}
		$new_db_script = $upgrade_path[count($upgrade_path)-1]['new_database_setup'];
		$new_data_script = $upgrade_path[count($upgrade_path)-1]['new_database_data'];
		$upgrade_data_scripts = array();
		$upgrade_database_setup = array();
		if(count($upgrade_path) > 1){
			for($i = count($upgrade_path)-2;$i >=0;$i--){
				$upgrade_data_scripts[] = $upgrade_path[$i]['upgrade_database_data'];
				$upgrade_database_setup[] = $upgrade_path[$i]['upgrade_database_setup'];
			}
		}
		$upgrade_path = 	array(	'new_database_setup'=>$new_db_script,
						'new_database_data'=>$new_data_script,
						'upgrade_database_data'=>$upgrade_data_scripts,
						'upgrade_database_setup'=>$upgrade_database_setup,
						'upgrade_roadmap'=>$upgrade_path);
		//	die();
		return 	$upgrade_path;
	}
	public function reset_auth_tokens($old_auth_token,$new_auth_token){
		$affected_rows = 0;
		$this->db->where('shopous_token',$old_auth_token);
		$this->db->update('stores',array('shopous_token'=>$new_auth_token));
		$affected_rows += $this->db->affected_rows();
		$this->db->where('shopkeeper_token',$old_auth_token);
		$this->db->update('stores',array('shopkeeper_token'=>$new_auth_token));
		$affected_rows += $this->db->affected_rows();
		return $affected_rows;		
	}
	function upgrade_user_database($id,$version){
		echo $id;
		echo "<br />";
		echo $version;
		$instance = $this->get_instance($id);
		echo $instance['version'];
		$database_name = $instance['db_name'];

		//echo json_encode($this->get_upgrade_path($version,$instance['version']));

		$this->migrate($database_name,$version);
		$instance = $this->get_instance($id);
		echo $instance['version'];
	}
	function setup_user_database($db_details,$version){
		flush();

		//create the new database.
		$rows = array();
		$rows[] = "drop database if exists `{$db_details['database']}`; ";
		$rows[] = "create database `{$db_details['database']}`;";
		$rows[] = "connect `{$db_details['database']}`;";
		$rows[] = "GRANT ALL PRIVILEGES ON `{$db_details['database']}`.* TO '{$db_details['username']}'@'localhost' IDENTIFIED BY '{$db_details['password']}';";

		$this->debug_message("writing out db setup file");
		if(!is_writable("temp/sqltemp.txt")){
			$this->errors[] = "SQLTEMP is not writable.";
			return false;
		}
		$file_handle = fopen("temp/sqltemp.txt",'w');
		foreach($rows as $row){
			fwrite($file_handle,$row . "\n",strlen($row)+1);
		}
		fclose($file_handle);
		$script_path = "temp/sqltemp.txt";
		$mysql_location = "";
		if(get_env() == "DEV"){
			$mysql_location = "/usr/local/mysql/bin/mysql";

		}
		else if(get_env() == "PROD")
		{
			$mysql_location = "mysql";
		}
		else{
			echo "ENvironment not set up in setup_user_Database()";
			die();
		}
		$command = "$mysql_location -u root -p".$this->master_db_pass . " -h localhost < {$script_path}";
		$this->debug_message("Running $command");
		exec($command . ' 2>&1',$output);

		//run migrations.
		$database_name = $db_details['database'];
		$this->migrate($database_name,$version);
		
		$this->debug_message( "Output:" . json_encode($output));
		$this->debug_message("<br />DB SETUP RUN.");
		//unlink('sqltemp.txt');
		return true;
	}
	function migrate($database_name,$version){
		$database_setup = $this->get_upgrade_path($version);
		//echo json_encode($database_setup);
		//die();

		$filename = $database_setup['new_database_setup'];
		$db_commands = read_file($filename);
		foreach($database_setup['upgrade_database_setup'] as $upgrade_file){
			$filename = $upgrade_file;
			$db_commands .= read_file($filename);
		}
		$filename = $database_setup['new_database_data'];
		$db_inserts = "";
		//$db_inserts = "\n--SET UP BASE DATABASE\n";
		$db_inserts .= read_file($filename);
		//$db_inserts .= "\n--SET UP UPGRADE DATA\n";
		foreach($database_setup['upgrade_database_data'] as $upgrade_file){
			$filename = $upgrade_file;
			$db_inserts .= read_file($filename);
		}

		$rows = array();
		$rows[] = "connect `$database_name`;";
		$rows = array_merge((array)$rows,(array)explode("\n",$db_commands));
		$rows = array_merge((array)$rows,(array)explode("\n",$db_inserts));

		$file_handle = fopen("temp/sqltemp.txt",'w');
		foreach($rows as $row){
			fwrite($file_handle,$row . "\n",strlen($row)+1);
		}
		fclose($file_handle);
		$script_path = "temp/sqltemp.txt";
		$mysql_location = "";
		if(get_env() == "DEV"){
			$mysql_location = "/usr/local/mysql/bin/mysql";

		}
		else if(get_env() == "PROD")
		{
			$mysql_location = "mysql";
		}
		else{
			echo "ENvironment not set up in setup_user_Database()";
			die();
		}
		$command = "$mysql_location -u root -p".$this->master_db_pass . " -h localhost < {$script_path}";
		$this->debug_message("Running $command");
		exec($command . ' 2>&1',$output);
	}
	function setup_analytics($userLogin, $password, $email, $alias, $auth_token, $site_url){
		$retval = array('result'=>'fail','errors'=>array());
		$messages = array();
		$analytics_base = "http://analytics.$this->server_name";


		//SitesManager.addSite (siteName, urls, ecommerce = '', excludedIps = '', excludedQueryParameters = '', timezone = '', currency = '', group = '', startDate = '')

		//add site
		$url = $analytics_base . "/?module=API&method=SitesManager.addSite".
			"&siteName=".urlencode($site_url).
			"&urls[0]=".urlencode($site_url).
			"&ecommerce=1".
			"&format=JSON&prettyDisplay=true".
			"&token_auth=".urlencode($auth_token);
		$result = curl_get($url);
		$result_object = json_decode($result);
		if(
			(isset($result_object->result) &&	$result_object->result == "error") || 
			!isset($result_object->value)
			){
				if(isset($result_object->message)){
					$retval['errors'] = "Set up analytics failed: " . $result_object->message;
				}else{
					$retval['errors'] = "Could not reach server.";
				}
			$retval['errors'] = $url;
			return $retval;
		}else{
			$messages[] = $result_object;
		
			$analytics_site_id = $result_object->value;
		}
		
		if($this->output_to_browser){
		echo "$url<br />";
		var_dump($result_object);	
		echo "<br />";
		}
		//get id of site
		//SitesManager.getSitesIdFromSiteUrl (url)
		$url = $analytics_base . "/?module=API&method=UsersManager.addUser".
			"&userLogin=".urlencode($userLogin).
			"&password=".urlencode($password).
			"&email=".urlencode($email).
			"&alias=".urlencode($alias).
			"&format=JSON&prettyDisplay=true".
			"&token_auth=".urlencode($auth_token);
		$result = curl_get($url);
		$result_object = json_decode($result);
		$messages[] = $result_object;
		if($this->output_to_browser){
		echo "$url<br />";
		var_dump($result_object);	
		echo "<br />";
		}		
		if(isset($result_object->result) && $result_object->result == "error"){
			$retval['errors'] = "Set up analytics failed: " . $result_object->message;
			return $retval;
		}
		$url = $analytics_base . "/?module=API&method=UsersManager.setUserAccess".
			"&userLogin=".urlencode($userLogin).
			"&access=view".
			"&idSites=".$analytics_site_id.
			"&format=JSON&prettyDisplay=true".
			"&token_auth=".urlencode($auth_token);
		$result = curl_get($url);
		$result_object = json_decode($result);
		$messages[] = $result_object;
		if($this->output_to_browser){
		echo "$url<br />";
		var_dump($result_object);	
		echo "<br />";

		}
		//UsersManager.getTokenAuth (userLogin, md5Password)
		if(!isset($result_object->result) || $result_object->result != "success"){
		return array('messages'=>$messages);
		}
		$url = $analytics_base . "/?module=API&method=UsersManager.getTokenAuth".
			"&userLogin=".urlencode($userLogin).
			"&md5Password=".urlencode(md5($password)).
			"&format=JSON&prettyDisplay=true".
			"&token_auth=".urlencode($auth_token);
		$result = curl_get($url);
		$result_object = json_decode($result);
		$messages[] = $result_object;
		$analytics_token_auth = $result_object->value;
		if($this->output_to_browser){
		echo "$url<br />";
		var_dump($result_object);	
		echo "<br />";
		}
		return array('analytics_base'=>$analytics_base,
					'analytics_site_id'=>$analytics_site_id,
					'analytics_token_auth'=>$analytics_token_auth,
					'messages'=>$messages,
					'password'=>$password);
	}
	function setup_config_file($config_file_location){
		//kept for legacy only.
		$config_file =read_file($config_file_location);
		$rows = explode("\n",$config_file);
		$configurables = array();
		/*for($i=0;$i<count($rows);$i++){
		//read till we find the configurable section.
			if($rows[$i] == "//start_shopous_read"){
				$i++;
				while($rows[$i] != "//end_shopous_read"){
					//read in configurables
					$config_key = substr($rows[$i],9,strpos($rows[$i],"'] ")-9);
					$config_value = $_POST["configurable_" . $config_key];
					$configurables[$config_key] = $config_value;
					$rows[$i] = "\$config['$config_key'] = \"$config_value\";";
					$i++;
				}
			}

		}*/

		$config_handle = fopen($config_file_location,'w');
		foreach($rows as $row){
		fwrite($config_handle,$row."\n",strlen($row)+1);
		}
		fclose($config_handle);
		//finished with this
		return true;

	}
	function setup_db_file($database_file_location,	$db_details){
		$dbconfig_handle = fopen($database_file_location,'r');
		$dbconfig_file = fread($dbconfig_handle,filesize($database_file_location));
		$rows = explode("\n",$dbconfig_file);
		fclose($dbconfig_handle);
		$end_editable = 0;
		$start_editable = 0;
		for($i=0;$i<count($rows);$i++){
			if($rows[$i] == "//start_shopous_read"){
				$start_editable = $i;
			}
			if($rows[$i] == "//end_shopous_read"){
				$end_editable = $i;
			}
		}
		//set up the array to go in the DB file.
		$db_details_formatted = array(
			'$db[\'default\'][\'username\'] = \'' . $db_details['username'] . '\';',
			'$db[\'default\'][\'password\'] = \'' . $db_details['password'] . '\';',
			'$db[\'default\'][\'database\'] = \'' . $db_details['database'] . '\';'
		);
		array_splice(	$rows,$start_editable,
						$end_editable - $start_editable,
						$db_details_formatted
						);
						
						
		/*$configurables = array();
		
		for($i=0;$i<count($rows);$i++){
		//read till we find the configurable section.
			if($rows[$i] == "//start_shopous_read"){
				$i++;
				while($rows[$i] != "//end_shopous_read"){
					//read in configurables
					$config_key = substr($rows[$i],16,8);
					$config_value = $db_details[$config_key];
					$rows[$i] = "\$db['default']['$config_key'] = \"$config_value\";";
					$i++;
				}
			}

		}
		*/
		$dbconfig_handle = fopen($database_file_location,'w');
		foreach($rows as $row){
		fwrite($dbconfig_handle,$row."\n",strlen($row)+1);
		}
		fclose($dbconfig_handle);
		//finished with this
		return true;
	}
	public function instance_url_taken($store_url){
		//	echo json_encode($this->master_db_connection);
		
		$this->db->where('store_url',$store_url);
		$q = $this->db->get('stores')->result_array();
		$result = (bool)$q;
		return $result;
	}
	public function debug_message($message){
		if(is_array($message)){
			$message = json_encode($message);
		}
		if($this->output_to_browser){
			echo $message . "<br/>";
		}
		
	}
	public function _make_database_username(){
		//shorten to 10 chars, add a random string to end
		$output = "u_";
		if(strlen($output) >= 10){
			$output = substr($output,0,10);
		}
			$output .= mt_rand(100000,999999);
		return $output;		
	}
	public function _make_database_name(){
		return "store_". $this->_make_database_username();
	}
}
