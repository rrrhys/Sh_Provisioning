<?

class Content_model extends CI_Model 
{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	function get_asset_base(){
	$base_url = $this->config->config['base_url'];
	$base_url = str_replace("http://","",$base_url);
	$base_url = str_replace("/","",$base_url);
	return "./assets/$base_url/";
	}
	function get_asset_base_display(){
	$base_url = $this->config->config['base_url'];
	$base_url = str_replace("http://","",$base_url);
	$base_url = str_replace("/","",$base_url);
	return "/assets/$base_url/";
	}
	function get_page_template($id){
		$this->db->where('id',$id);
		$q = $this->db->get('configurables')->row_array();
		if(!$q){
			$q['name'] = "";
			$q['description'] = "";
			$q['title'] = "";
			$q['id'] = "";
		}
		return $q;
		
	}
	function get_page_templates(){
		$q = $this->db->get('configurables')->result_array();

		return $q;
		
	}
	function get_configurable($configurable_name){
		$this->db->where('name',$configurable_name);
		$q = $this->db->get('configurables')->row_array();
		return $q['description'];
	}
	function get_page_merged($page_name,$extra_vars = array()){
		//supply an array of k->v pairs in $extra_vars to do extra merging req'd.
		$this->db->where('name',$page_name);
		$q = $this->db->get('configurables')->row_array();
		if($q){
		$q['description'] = $this->_merge_fields($q['description'],$extra_vars);
		$q['description'] = nl2br($q['description']);
		return $q;
		}
		
		else
		return false;
	}
	
	function _merge_fields($text,$extra_vars = array()){
/*	$instance_vars = $this->instance_model->get();
		foreach($instance_vars as $k=>$v){
			$text = str_replace("#" . $k . "#",$instance_vars[$k]['description'],$text);
		}*/
		foreach($extra_vars as $k=>$v){
			$text = str_replace("#" . $k . "#",$extra_vars[$k],$text);
		}
	return $text;
	}
	
	function update_page($id = "",$title,$description,$name){
		$update = array();
		if(!$id == ""){
			$this->db->where('id',$id);
		}
		else
		{
			$update['id'] = gen_uuid();
		}
		
		if($title !== false){$update['title'] = $title;}
		if($name !== false){$update['name'] = $name;}
		if($description !== false){$update['description'] = $description;}
		if(isset($update['id'])){
			$this->db->insert('configurables',$update);
		}
		else
		{
			$this->db->update('configurables',$update);
		}
		return $update;
	}

}