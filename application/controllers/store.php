<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Store extends CI_Controller {
	var $analytics_auth_token = "";
	function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->model('store_model');
		
		$this->analytics_auth_token = $this->config->config['analytics_auth_token'];

		if(!$this->check_login()){
			redirect("/login/");
		}
		
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
	function guid(){
		echo gen_uuid();	
	}
	function index(){
		$data = $this->_base_data();
		$data['heading'] = "Administrator Console";
		$data['guid'] = gen_uuid();
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
	function migrate_to_current($store_id){
		$store = $this->store_model->get_instance($store_id);
		$current_version = $this->store_model->get_latest_release();
		$migration_output = $this->store_model->migrate($store['db_name'],$current_version['version'],$store['version'],$store);
		echo "<br>";
		echo "migration output:";
		echo json_encode($migration_output);
	}
	function list_stores_json(){
		$stores = $this->store_model->list_instances_active(false);
		echo json_encode(array('result'=>'success','stores'=>$stores));
	}
	function list_analytics_json(){
		$analytics = $this->store_model->list_analytics();
		echo json_encode(array('result'=>'success','analytics'=>$analytics));
	}
	function get_current_version(){
		echo json_encode($this->store_model->get_latest_release());
	}
	function delete_store_json($store_id){
		$result = $this->store_model->delete_store($store_id);
		echo json_encode($result);
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
		$data['shortcuts'] = array(array('link'=>'/store/list_analytics','name'=>'List Analytics'));
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
	function unit_test(){
		//work in progress.
		$result = array();
		$result['result'] = 'success';
			//creating a store.
					$product_url = "http://store.tester.com";
					$store_name = "_TESTER_STORE_NAME_";
					$email_address = "TESTER@TESTER.COM";
					$version = "";
					$result['create_store'] =  $this->store_model->create_store($version,
				$product_url,
				$store_name,
				$email_address);
				if($result['create_store']['result'] == "success"){
					$result['delete_store'] = $this->store_model->delete_store($result['create_store']['id']);
					if($result['delete_store']['result'] == "success"){
						//delete passed.
					}else{
						$result['result'] = "fail";
					}
				}else{
					$result['result'] = "fail";
				}
				echo json_encode($result);
		}
	function create_store_json(){
			$retval = array('result'=>'fail','errors'=>array());

			$product_url = $this->input->post('store_url');
			$store_name = $this->input->post('store_name');
			$email_address = $this->input->post('administrator_email');
			$version = $this->input->post('version');

			

			$errors = array();

			$result = $this->store_model->create_store($version,
				$product_url,
				$store_name,
				$email_address);
			if($result['result'] == "success"){
			//send the user a success email
				$email_body = $this->content_model->get_page_merged('newStoreReady',array('url'=>$product_url,
																						'store_name'=>$store_name,
																						'email_address'=>$email_address,
																						'password'=>$result['user_password']));
					$this->email_model->queue_email($this->content_model->get_configurable('fromEmailAddress'),
					$email_address,
					"New Account Created",
					$email_body['description'],
					"", 
					true);
			}
			echo json_encode($result);
	}


}