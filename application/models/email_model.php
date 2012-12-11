<?

class Email_model extends CI_Model 
{
	var $carbon_copy_email = "correspondence@shopous.com.au";
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	function queue_email($email_from,$email_to,$email_subject,$email_body,$dont_send_before = "", $process_queue = true){
	
		$insert = array();
		$insert['email_subject'] = $email_subject;
		$insert['email_to'] = $email_to;
		$insert['email_from'] = $email_from;
		$insert['email_body'] = $email_body;
		$insert['id'] = get_uuid();
		if($dont_send_before != ""){
		$insert['dont_send_before']= $dont_send_before;
		}
		$this->db->insert('email',$insert);
		
	
		if($process_queue){
		$this->process_queue();
		}
		return $insert;
		
	}
	function process_queue(){
		$this->db->where('(dont_send_before < now() OR isnull(dont_send_before)) AND isnull(email_sent)','',false);
		$q = $this->db->get('email')->result_array();
		
		foreach($q as $email){
		//send email
		$this->load->library('email');
		$config['mailtype'] = "html";
		
		//sendgrid integration
	if(get_env() == "PROD"){
		$config['protocol'] = "smtp";
		$config['smtp_host'] = "smtp.sendgrid.net";
		$config['smtp_user'] = "rrrhys";
		$config['smtp_pass'] = "Rrrhys01";
		$config['smtp_port'] = 587;
		$config['crlf'] = "\r\n";
		$config['newline'] = "\r\n";
	}
		//end sendgrid integration
		
		$this->email->initialize($config);
		$this->email->from($email['email_from'],$email['email_from']);
		
		$this->email->to($email['email_to']); 

		$this->email->subject($email['email_subject']);
		$this->email->message("<img src='http://www.shopous.com.au/shopous240.png' /><br />".$email['email_body'] . "<br /><br />" . "Email reference " . $email['id']);	

		$this->email->send();
		$this->email->to($this->carbon_copy_email); 

		$this->email->subject("BCC: " . $email['email_subject']);

		$this->email->send();
		//tell db
		$this->db->where('id',$email['id']);
		$this->db->update('email',array('email_sent'=>time_to_mysql(now())));
		
		}
	}

}