<input type="hidden" name="page_id" id="page_id" value="<?=$page_content['id']?>" />
<label for="page_title">Page Title</label><br />
<input type="text" name="page_title" id="page_title" value="<?=$page_content['title']?>"  class="big_field" /><br /><br />
<label for="page_name">Page Name</label><br />
<input type="text" name="page_name" id="page_name" value="<?=$page_content['name']?>" class="big_field" /><br /><br />
<label for="configurable">Page Contents</label><br />
<textarea name="configurable" id="configurable" cols="30" rows="10" class="ckeditor"><?=$page_content['description']?></textarea>


<input type="submit" value="Save Configurable Item" id="save_configurable" />
<script type="text/javascript">
$(function(){
	$("#save_configurable").click(function(){
		var oEditor = CKEDITOR.instances.configurable;
		//alert(oEditor.getData());
		$.post("/store/save_configurable_json/",{
			'description':oEditor.getData(),
			'title':$("#page_title").val(),
			'name':$("#page_name").val(),
			'id':$("#page_id").val()
			
		},function(data){
			$("#debug_information").html(data);
		})
	});
});
</script>