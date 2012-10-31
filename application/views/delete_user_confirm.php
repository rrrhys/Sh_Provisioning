
<div id="delete_form">
	Are you sure you want to delete this user?<br />
	<input type="submit" value="Yes" id="delete_user" />
	<input type="hidden" name="login" id="login" value="<?=$login?>" />
</div>
<div id="delete_successful" style="display:none;">The delete was successful!</div>
<div id="delete_errors" style="display:none;"></div>
<script type="text/javascript">
	
	$(function(){
		$("#delete_user").click(function(){
			$("#delete_form").hide();
			$.post("/store/delete_user_json/" + $("#login").val(),{
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