<?

class Audit_model extends CI_Model 
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	function get($limit=100, $offset=0){
	//	$this->db->limit($limit);
	//	$this->db->offset($offset);
		$q = $this->db->get('audit_log')->result_array();
		return $q;
	}
	function record($action,$result,$messages,$user_data){
	//	echo json_encode($user_data);
		if(is_array($messages)){
			$messages=  json_encode($messages);
		}
		$this->db->set('action',$action);
		$this->db->set('result',$result);
		$this->db->set('messages',$messages);
		$this->db->set('time_logged','now()',false);
		$this->db->set('ip_address',$user_data['ip_address']);
		$this->db->set('session_id',$user_data['session_id']);
		$this->db->insert('audit_log');
	}
}