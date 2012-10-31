
<?//=form_open("/store/create_store/")?>
<div id='store_error'>
	<p>There were errors creating your store!<br />
		Details:</p>
		<ul id="store_errors">
			
		</ul>
	</div>
<div id="create_store" class="big_field_form">
<input type="hidden" name="version" id="version" value="<?=$release['version']?>" />

<table>
	<tr>
		<td><label for="store_name">Store Name</label><br />
			<input type="text" id="store_name" name="store_name" value="" class="big_field"></td>
		<td id="store_name_validation"></td>
	</tr>
	<tr>
		<td><label for="store_url">Store URL</label><br />
			<input type="text" id="store_url" name="store_url" value="" class="big_field"></td>
		<td id="store_url_validation"></td>
	</tr>
	<tr>
		<td><label for="administrator_email">Admin Email</label><br />
			<input type="text" id="administrator_email" name="administrator_email" value="" class="big_field"></td>
		<td id="administrator_email_validation"></td>
	</tr>
	<tr><td><input type="submit" id="submit_form" /></td>
		<td id="submit_form_validation"></td></tr>
</table>
</div>
<div id="store_created" style="display: none;">
	<p>The new store has been set up, <a id="new_store_link">access it here!</a><br />
		<a id="impersonate_shopkeeper">Impersonate shopkeeper</a> or <a id="shopous_admin">Login as Shopous Admin</a>
		The following steps were carried out successfully:</p>
		<ul id="steps_completed"></ul>
	
</div>

<script type="text/javascript">
	$(function(){
		
		$("#submit_form").click(function(){
			$("#store_error").hide();
			$.post("/store/create_store_json/",{
				"ci_csrf_token": $("#csrf_protection").val(),
				"administrator_email":$("#administrator_email").val(),
				"version":$("#version").val(),
				"store_url":$("#store_url").val(),
				"store_name":$("#store_name").val()
			},function(data){
				$("#debug_information").text(data);
				var dataobj = $.parseJSON(data);
				if(dataobj.result == "success"){
					$("#store_created").show();
					$("#create_store").hide();
					$("#steps_completed").html("");
					$.each(dataobj.steps_completed,function(index, val){
						$("#steps_completed").append("<li>" + val + "</li>")
					});
					$("#new_store_link").attr('href',dataobj.store_url);
					$("#impersonate_shopkeeper").attr('href',dataobj.shopkeeper_token);
					$("#shopous_admin").attr('href',dataobj.shopous_token);
				}
				else
				{
					$("#store_error").show();
					$("#store_errors").html("");
							$("#store_errors").append("<li>" + dataobj.errors.error + ": " + dataobj.errors.description + "</li>");
				}
			})
		});
		$("#store_url").blur(function(){
			if($("#store_url").val().substr(0,7) != "http://"){
				$("#store_url").val("http://" + $("#store_url").val());
			}
			isStoreURLOK($("#store_url").val(),function(result){
				if(!result.success){
					$("#store_url_validation").text(result.messages);
				}
				else
				{
					$("#store_url_validation").text("");
				}
			});
			
		});
		$("#store_name").blur(function(){
				$("#store_url").val(makeStoreURL($("#store_name").val()));
			isStoreNameOK($("#store_name").val(),function(result){
				if(!result.success){
					$("#store_name_validation").text(result.messages);
				}
				else
				{
					$("#store_name_validation").text("");
				}
			});
			
		});
		
	})
	function makeStoreURL(storename){
		var store_url = storename.toLowerCase();
		store_url = store_url.replace( /[^a-z]/g, '');
		if(store_url != ""){
			
			store_url = "http://" + store_url+ "." + $("#server_name").val();
		}
		return store_url;
	}
	function isStoreNameOK(store_name,callback){
		$.post("/validation/store_name_ok",{
			'store_name':store_name
		},function(data){
			var data = $.parseJSON(data);
			var result = {'success': false,
							'messages': ''};
			if(data.result == false){
				result.messages = "That name is not acceptable";
			}else if(data.result == true){
				result.success = true;
			}else {
				result.messages  = "Unspecified error.";
			}
			callback(result);
		})		
	}
	function isStoreURLOK(store_url,callback){
		/*var base_url = "<?=$server_name?>";
		var instance_url = "http://" + store_name + "." + base_url; */
		$.post("/validation/instance_url_taken",{
			'instance_url':store_url
		},function(data){
			var data = $.parseJSON(data);
			var result = {'success': false,
							'messages': ''};
			if(data.result == true){
				result.messages = "That URL is taken.";
			}else if(data.result == false){
				result.success = true;
			}else {
				result.messages  = "Unspecified error.";
			}
			callback(result);
		})
		
	}
</script>