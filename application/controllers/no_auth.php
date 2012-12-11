<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class No_auth extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->helper('form');
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

	function create_store(){

		$create_store_automatically = false;

		header('Access-Control-Allow-Origin: *.shopous.com.au');	
		$store_address = $this->input->post('store_address');
		$store_name = $this->input->post('store_name');
		$full_name = $this->input->post('full_name');
		$email_address = $this->input->post('email_address');
		$email_body = 	"Store Address: $store_address<br />".
						"Email Address: $email_address<br />".
						"Full Name: $full_name<br />".
						"Store Name: $store_name";
			$this->email_model->queue_email("Rhys.Williams@shopous.com.au",
			"Rhys.Williams@shopous.com.au",
			"New Account Requested",
			$email_body,
			"", 
			true);
	echo json_encode(array('result'=>'success'));
	
	
	}
	function list_stores_json(){
		header('Access-Control-Allow-Origin: *');	
		$stores = $this->store_model->list_instances_active(true);
		foreach($stores as &$store){
			$store['input_url'] = $store['store_url'] . "/store/home";
			$store['thumb_url'] = $this->store_model->get_thumbnail_url($store['store_url'] . "/store/home");
			
		}
		echo json_encode($stores);
	}

}