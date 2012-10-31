
<div id="delete_form">
	Are you sure you want to delete this store?<br />
	<input type="submit" value="Yes" id="delete_store" />
	<input type="hidden" name="instance_id" id="instance_id" value="<?=$instance_id?>" />
</div>
<div id="delete_successful" style="display:none;">The delete was successful!</div>
<div id="delete_errors" style="display:none;"></div>
<script type="text/javascript">
	
	$(function(){
		$("#delete_store").click(function(){
			$("#delete_form").hide();
			$.post("/store/delete_store_json/" + $("#instance_id").val(),{
				"ci_csrf_token": $("#csrf_protection").val()
			},function(data){
				$("#debug_information").text(data);
				var dataobj = $.parseJSON(data);
				if(dataobj.result == "success"){
					$("#delete_successful").show();
				}
				else{
					if(dataobj.errors != undefined){
						$("#delete_errors").show();
						$.each(dataobj.errors,function(index,error){
							$("#delete_errors").append(error + "<br />");
						});
					}
				}
				
			});
		});
	});
</script>