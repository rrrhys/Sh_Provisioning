<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
	}
	function _base_data(){
		$data = array();
		$data['error_flash'] = $this->session->flashdata('error_flash');
		$data['server_name'] = "";
		return $data;
	}
	function index(){
		redirect("/login/form/");
	}
	function form(){
		if($this->input->post('username')){
			$login_details = array();
			$login_details['username'] = $this->input->post('username');
			$login_details['pass'] = sha1($this->input->post('password'));
			if($login_details['username'] = "rrrhys" && $login_details['pass'] == "2bf2c6e19819ac408a4d297a9f5f88ddf222895e"){
				$this->session->set_userdata('logged_in',true);
				redirect("/store/");
			}
			else
			{
				redirect("/login/unauthorised");
			}
		}
		else
		{
			$data = $this->_base_data();
			$data['heading'] = "Login";
			$this->load->view('header',$data);	
			$this->load->view('login',$data);
			$this->load->view('footer',$data);		
		}
		
	}
	function unauthorised(){
		$data = $this->_base_data();
		$data['heading'] = "Unauthorised";
		$this->load->view('header',$data);	
		$this->load->view('unauthorised',$data);
		$this->load->view('footer',$data);		
	}
}