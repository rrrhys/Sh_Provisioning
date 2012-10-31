
<a href="#" id="refresh_table">Refresh</a>
<table>
	<tr>
		<th>Page Name</th>
		<th>Page Title</th>
		<th>Edit</th>
	</tr>
	<tbody id="loading">
		<tr>
			<td colspan=10>Loading</td>
		</tr>
	</tbody>
	<tbody id="list" style="display:none;"></tbody>
	<tbody id="none" style="display:none;">
		<tr>
			<td colspan=10>There are no configurables!</td>
		</tr>
	</tbody>
</table>
<script type="text/javascript">
	function init_table(){
		$("#none").hide();
		$("#list").hide();
		$("#list").html("");
		$("#loading").show();
		$.post("/store/list_configurables_json/",{},function(data){
			var dataobj = $.parseJSON(data);
			$("#debug_information").text(data);
			if(dataobj.result == "success"){
				$("#loading").hide();
				if(dataobj.configurables.length > 0){
					$("#list").show();
					$.each(dataobj.configurables,function(index,conf){
						var row_html = "<tr>" +
						"<td>" + conf.name + "</td>" + 
						"<td>" + conf.title + "</td>" + 
										"<td><a href='/store/configurable/" + conf.id + "'>Edit</a></td></tr>";
						$("#list").append(row_html);
					})
				}
				else
				{
					$("#none").show();
					$("#list").hide();
				}
			}
		})
	}
	$(function(){
		init_table();
		$("#refresh_table").click(function(){init_table();})
	});
</script>