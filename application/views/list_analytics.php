
<a href="#" id="refresh_table">Refresh</a>
<table>
	<tr>
		<th>IDSite</th>
		<th>Store URL</th>
		<th>Delete</th>
	</tr>
	<tbody class="loading">
		<tr>
			<td colspan=10>Loading</td>
		</tr>
	</tbody>
	<tbody id="analytics_list" style="display:none;"></tbody>
	<tbody id="no_analytics" style="display:none;">
		<tr>
			<td colspan=10>There are no analytics sites!</td>
		</tr>
	</tbody>
</table>
<table>
	<tr>
		<th>Login</th>
		<th>Email</th>
		<th>Delete</th>
	</tr>
	<tbody class="loading">
		<tr>
			<td colspan=10>Loading</td>
		</tr>
	</tbody>
	<tbody id="analytics_user_list" style="display:none;"></tbody>
	<tbody id="no_analytics_users" style="display:none;">
		<tr>
			<td colspan=10>There are no analytics users!</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	function init_tables(){
		$("#no_stores").hide();
		$("#analytics_list").hide();
		$("#analytics_list").html("");
		$("#no_analytics_users").hide();
		$("#analytics_user_list").hide();
		$("#analytics_user_list").html("");
		$("#loading").show();
		$.post("/store/list_analytics_json/",{},function(data){
			var dataobj = $.parseJSON(data);
			$("#debug_information").text(data);
			if(dataobj.result == "success"){
				$(".loading").hide();
				if(dataobj.analytics.sites.length > 0){
					$("#analytics_list").show();
					$.each(dataobj.analytics.sites,function(index,site){
						var row_html = "<tr>" +
										"<td>" + site.idsite + "</td>" + 
										"<td>" + site.name + "</td>" +
										"<td><a href='/store/delete_analytics_site/" + site.idsite + "'>Delete</a></td></tr>";
						$("#analytics_list").append(row_html);
					})
				}
				else
				{
					$("#no_analytics").show();
					$("#analytics_list").hide();
				}
				if(dataobj.analytics.users.length > 0){
					$("#analytics_user_list").show();
					$.each(dataobj.analytics.users,function(index,user){
						var row_html = "<tr>" +
										"<td>" + user.login + "</td>" + 
										"<td>" + user.email + "</td>" +
										"<td><a href='/store/delete_analytics_user/" + user.login + "'>Delete</a></td></tr>";
						$("#analytics_user_list").append(row_html);
					})
				}
				else
				{
					$("#no_analytics_users").show();
					$("#analytics_user_list").hide();
				}
			}
		})
	}
	$(function(){
		init_tables();
		$("#refresh_table").click(function(){init_tables();})
	});
</script>