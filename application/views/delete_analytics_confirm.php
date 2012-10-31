
<div id="delete_form">
	Are you sure you want to delete this analytics site?<br />
	<input type="submit" value="Yes" id="delete_analytics_site" />
	<input type="hidden" name="idsite" id="idsite" value="<?=$idsite?>" />
</div>
<div id="delete_successful" style="display:none;">The delete was successful!</div>
<div id="delete_errors" style="display:none;"></div>
<script type="text/javascript">
	
	$(function(){
		$("#delete_analytics_site").click(function(){
			$("#delete_form").hide();
			$.post("/store/delete_analytics_site_json/" + $("#idsite").val(),{
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