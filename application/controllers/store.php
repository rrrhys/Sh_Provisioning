<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->helper('form');

		if(!$this->check_login()){
			redirect("/login/");
		}
		$this->load->model('store_model');
		$this->load->model('audit_model');
		$this->load->model('email_model');
		$this->load->model('content_model');
		$this->audit_model->record("Store Admin Action" . $_SERVER['REQUEST_URI'],
									"",
									json_encode($_POST),
									$this->session->all_userdata());
	}
	function _base_data(){
		$data = array();
		$data['error_flash'] = $this->session->flashdata('error_flash');
		$data['server_name'] = $this->store_model->server_name;
		return $data;
	}
	function check_login(){
		$logged_in = $this->session->userdata('logged_in');
		return $logged_in;
	}
	function upgrade_user_database($id,$version){
		echo $this->store_model->upgrade_user_database($id,$version);
	}
	function save_configurable_json(){
		/*'description':oEditor.getData(),
		'title':$("#page_title").val(),
		'name':$("#page_name").val()*/
		$description = $this->input->post('description');
		$title = $this->input->post('title');
		$name = $this->input->post('name');
		$id = $this->input->post('id');
		$result = $this->content_model->update_page($id,$title,$description,$name);
		echo json_encode($result);
	}
	function configurable($id = ""){
		
		$content = $this->content_model->get_page_template($id);

		$data = $this->_base_data();
		$data['page_content'] = $content;
		$data['heading'] = "Edit Page named {$content['name']}";
		$this->load->view('header',$data);	
		$this->load->view('configurable',$data);
		$this->load->view('footer',$data);
	}
	function _fail_create_store($retval){
		echo json_encode($retval);
		die();
	}
	function index(){
		$data = $this->_base_data();
		$data['heading'] = "Administrator Console";
		$this->load->view('header',$data);	
		$this->load->view('index',$data);
		$this->load->view('footer',$data);		
	}
	function list_stores(){
		$data = $this->_base_data();
		$data['heading'] = "List Stores";
		$this->load->view('header',$data);
		$this->load->view('list_stores',$data);
		$this->load->view('footer',$data);		
	}
	function list_configurables(){
		$data = $this->_base_data();
		$data['heading'] = "List Configurables";
		$this->load->view('header',$data);
		$this->load->view('list_configurables',$data);
		$this->load->view('footer',$data);		
	}
	function list_analytics(){
		$data = $this->_base_data();
		$data['heading'] = "List Analytics";
		$this->load->view('header',$data);
		$this->load->view('list_analytics',$data);
		$this->load->view('footer',$data);
	}
	function list_stores_json(){
		$stores = $this->store_model->list_instances_active();
		echo json_encode(array('result'=>'success','stores'=>$stores));
	}
	function list_analytics_json(){
		$analytics = $this->store_model->list_analytics();
		echo json_encode(array('result'=>'success','analytics'=>$analytics));
	}
	function delete_store_json($store_id){
		//function will carry out delete regardless of failed sections.
		$retval = array('result'=>'fail','errors'=>array());
		$result = $this->store_model->delete_from_analytics($store_id);
		if($result['result'] == 'fail'){
			$retval['errors'][] = $result['errors'];
			echo "an";
		}
		$result = $this->store_model->delete_from_filesystem($store_id);
		if($result['result'] == 'fail'){
			$retval['errors'][] = $result['errors'];
			echo "FS";
		}
		$result = $this->store_model->delete_from_db($store_id);
		if($result['result'] == 'fail'){
			$retval['errors'][] = $result['errors'];
			echo "DB";
		}
		if(count($retval['errors']) == 0){
			$retval['result'] = "success";
		}

		echo json_encode($retval);
	}
	function delete_analytics_site_json($idsite){
		//function will carry out delete regardless of failed sections.
		$retval = array('result'=>'fail','errors'=>array());
		$result = $this->store_model->delete_from_analytics_by_idsite($idsite);
		if($result['result'] == 'fail'){
			$retval['errors'][] = $result['errors'];
			echo "an";
		}
		if(count($retval['errors']) == 0){
			$retval['result'] = "success";
		}

		echo json_encode($retval);
	}
	function delete_user_json($login){
		//function will carry out delete regardless of failed sections.
		$retval = array('result'=>'fail','errors'=>array());
		$result = $this->store_model->delete_analytics_user($login);
		if($result['result'] == 'fail'){
			$retval['errors'][] = $result['errors'];
			echo "an";
		}
		if(count($retval['errors']) == 0){
			$retval['result'] = "success";
		}

		echo json_encode($retval);		
	}
	function list_configurables_json(){
		$configurables = $this->content_model->get_page_templates();
		echo json_encode(array('result'=>'success','configurables'=>$configurables));
	}
	function delete_store($instance_id){
		$data = $this->_base_data();
		$data['heading'] = "Delete Store";
		$data['instance_id'] = $instance_id;
		$data['shortcuts'] = array(array('link'=>'/store/list_stores','name'=>'List Stores'));
		$this->load->view('header',$data);
		$this->load->view('delete_store_confirm',$data);
		$this->load->view('footer',$data);		
	}
	function delete_analytics_site($idsite){
		$data = $this->_base_data();
		$data['heading'] = "Delete Site";
		$data['idsite'] = $idsite;
		$this->load->view('header',$data);
		$this->load->view('delete_analytics_confirm',$data);
		$this->load->view('footer',$data);		
	}
	function delete_analytics_user($login){
		$data = $this->_base_data();
		$data['heading'] = "Delete Analytics User";
		$data['login'] = $login;
		$this->load->view('header',$data);
		$this->load->view('delete_user_confirm',$data);
		$this->load->view('footer',$data);		
	}
	function create_store(){
			$data = $this->_base_data();
			$data['heading'] = "Create a Store";
			$data['release'] = $this->store_model->get_latest_release();
			$this->load->view('header',$data);
			$this->load->view('create_store',$data);
			$this->load->view('footer',$data);
	}
	function create_store_json(){
			$retval = array('result'=>'fail','errors'=>array());
			//details that will be saved for this user
			$continue_setup = true;
			$product_url = $this->input->post('store_url');
			$store_name = $this->input->post('store_name');
			$email_address = $this->input->post('administrator_email');
			//$rows[] = "GRANT ALL PRIVILEGES ON `{$db_details['database']}`.* TO '{$db_details['username']}'@'localhost' IDENTIFIED BY '{$db_details['password']}';";
			
			$database_username = $this->_make_database_username($this->input->post('user_id'));
			$database_password = uniqid() . "shopoussalt20111208";
			$database_name = $this->_make_database_name($this->input->post('username'));
			
			$db_details = array();
			$db_details['database'] = $database_name;
			$db_details['username'] = $database_username;
			$db_details['password'] = $database_password;
			
			$password = uniqid();
			$analytics_password = uniqid();
			$version = $this->input->post('version');
			$analytics_site_id = 0;
			
			$shopkeeper_token = gen_uuid();
			$shopous_token = gen_uuid();
			
			if(strpos($this->store_model->server_name,"devshopous.dev") > -1){
			//if($_SERVER['SERVER_NAME'] == "provisioning." . $this->store_model->dev_base){
			$analytics_auth_token = "e0ab1c86cc5181b9db6924159a19ac82";
			}
			else
			{
			$analytics_auth_token = "1f2700f3605ecb55ec239dc286a5b23a";
			}
			$errors = array();
			//$errors[] = array('error'=>'URL Taken','description'=>'That URL is already taken.');
		
			if($this->store_model->instance_url_taken($product_url)){
				$continue_setup = false;
				$retval['errors'] = array('error'=>'URL Taken','description'=>'That URL is already taken.');
			}
			if(!$continue_setup){
				$this->_fail_create_store($retval);
				die();
			}
			$analytics_site = $this->store_model->setup_analytics($database_username,
																	$analytics_password,
																	$email_address,
																	$database_username,
																	$analytics_auth_token,
																	$product_url);
			if(!isset($analytics_site['analytics_site_id'])){
				$continue_setup = false;
				//setup failed due to analytics error.
				$retval['errors'] = array('error'=>'Analytics setup failed','description'=>$analytics_site['errors']);
				//var_dump($analytics_site);
			//	echo "(roll back analytics setups)";
			}
			if(!$continue_setup){
				$this->_fail_create_store($retval);
				die();
			}
			$analytics_site_id = $analytics_site['analytics_site_id'];
			
			$result = $this->store_model->create_store($version,
				$product_url,
				$store_name,
				$db_details,
				$shopkeeper_token,
				$shopous_token,
				$analytics_site,
				$email_address,
				$password);
			//send the user a success email
				$email_body = $this->content_model->get_page_merged('newStoreReady',array('url'=>$product_url,
																						'store_name'=>$store_name,
																						'email_address'=>$email_address,
																						'password'=>$password));
					$this->email_model->queue_email("Rhys.Williams@shopous.com",
					$email_address,
					"New Account Created",
					$email_body['description'],
					"", 
					true);
			echo json_encode($result);
			/*if($result['success'] == 'false'){
					$continue_setup = false;
					//setup failed due to analytics error.
					$retval['errors'] = array('error'=>implode("<br />",$result['messages']),'description'=>$analytics_site['errors']);
					//var_dump($analytics_site);
					$this->_fail_create_store($retval);
					die();
					echo "(roll back m setainups)";
			}*/
	}
	function _make_database_name($input){
		return "store_". $this->_make_database_username($input);
	}
	function _make_database_username($input){
		//shorten to 10 chars, add a random string to end
		$output = "u_".str_replace(" ","",$input);
		if(strlen($output) >= 10){
			$output = substr($output,0,10);
		}
			$output .= mt_rand(100000,999999);
		return $output;

	}
}