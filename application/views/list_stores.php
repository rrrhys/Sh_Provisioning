
<a href="#" id="refresh_table">Refresh</a>
TO DO: Check Migration Works. /store/upgrade_user_database($id,$version) should upgrade store with $id to $version.
<table>
	<tr>
		<th>Store Name</th>
		<th>Store URL</th>
		<th>User Login</th>
		<th>Shopous Login</th>
		<th>Delete</th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
	</tr>
	<tbody id="loading">
		<tr>
			<td colspan=10>Loading</td>
		</tr>
	</tbody>
	<tbody id="store_list" style="display:none;"></tbody>
	<tbody id="no_stores" style="display:none;">
		<tr>
			<td colspan=10>There are no stores!</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	function init_table(){
		$("#no_stores").hide();
		$("#store_list").hide();
		$("#store_list").html("");
		$("#loading").show();
		$.post("/store/list_stores_json/",{},function(data){
			var dataobj = $.parseJSON(data);
			$("#debug_information").text(data);
			if(dataobj.result == "success"){
				$("#loading").hide();
				if(dataobj.stores.length > 0){
					$("#store_list").show();
					$.each(dataobj.stores,function(index,store){
						var row_html = "<tr>" +
										"<td>" + store.store_name + "</td>" + 
										"<td><a href='" + store.store_url + "'>" + store.store_url + "</a></td>" +
										"<td><a href=" + store.store_url + "/user/login_by_token/" + store.shopkeeper_token + " target='_blank'>Login as User</a></td>" + 
										"<td><a href=" + store.store_url + "/user/login_by_token/" + store.shopous_token + " target='_blank'>Login as Shopous</a></td>" + 
										"<td>" + store.id + "</td>" + 
										"<td><a href='/store/delete_store/" + store.id + "'>Delete</a></td></tr>";
						$("#store_list").append(row_html);
					})
				}
				else
				{
					$("#no_stores").show();
					$("#store_list").hide();
				}
			}
		})
	}
	$(function(){
		init_table();
		$("#refresh_table").click(function(){init_table();})
	});
</script>