<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Validation extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		if(!$this->check_login()){
			redirect("/login/");
		}
		$this->load->model('store_model');
	}
	function check_login(){
		$logged_in = $this->session->userdata('logged_in');
		return $logged_in;
	}

	function store_name_ok(){
		$store_name = $this->input->post('store_name');
		$result = array('request'=>$store_name,'result'=>true);
		echo json_encode($result);
	}
	function instance_url_taken(){
		$instance_url = $this->input->post('instance_url');
		$result = array('request'=>$instance_url,'result'=>true);
		if($this->store_model->instance_url_taken($instance_url)){
			$result['result'] = true;
		}
		else
		{
			$result['result'] = false;
		}
		echo json_encode($result);
	}

}