<!DOCTYPE html>
<html lang="en">

<head>
	<title><?=isset($title) ? $title : "Shopous Store Manager";?></title>
	<script type="text/javascript" src="/scripts/jquery-1.4.2.js"></script>
	<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="/scripts/jquery.js"></script>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>

	<style type="text/css">
	body {
		padding: 0px;
		margin: 0px;
		font-family: Helvetica, Calibri, Arial;
		color: #1f373b;
	}
	h1 {
		color: #3c6a72;
		border-bottom: 1px solid #ccc;
		font-size: 24pt;
		font-weight: normal;
	}
	.subtext {
	font-size: 8pt;
	color: #000;	
	
	}
	table {
		width: 600px;
		margin: 10px auto;
		border: 1px solid #ccc;
		background-color: rgba(200,200,200,0.1);
	}
	th {
		border-bottom: 1px solid #ccc;
	}
	#container_background{
		width: 100%;
		height: 100%;
		position: fixed;
		background-image: url("/images/store.jpg");
		background-position-x: 0%;
		background-position-y: 80%;
		opacity: 0.8;
		z-index: -11;
	}
	#container {
		max-width: 900px;
		min-width: 700px;
		margin: 0px auto;

	}
	#container_right {
		padding: 20px;
		background-color: rgba(255,255,255,0.8);
		float: left;
		width: 800px;
		min-height: 2000px;
		-moz-box-shadow: 0 0 5px #333;
		-webkit-box-shadow: 0 0 5px #333;
		box-shadow: 0 0 8px #333;
		border-right: 1px #ccc solid;
		border-left: 1px #ccc solid;
	}
	#shortcuts {
		width: 100%;
		padding-bottom: 5px;
		margin-bottom: 10px;
		border-bottom: 1px solid #ccc;
	}
	
	.big_field {
		font-size: 18pt;
		border-radius: 8px;
		margin-left: 5px;
		color: #666;
		width: 400px;
	}
	.big_field_form {
		width: 440px;
		margin: 20px auto;
	}
	label {
		color: #333;
		font-weight: bold;
	}
	#debug_information{
		font-size: 10pt;
		display: none;
		color: #b1220b;
		width: 700px;
		margin: 10px auto;
		overflow: auto;
	}
	#show_debug_information:hover {
		color: #ff0000;
		cursor: pointer;
	}
	</style>
</head>
<body>
	
		<div id="container_background"></div>
		<div id="container">
			<div id="container_right">
				<input type="hidden" value="<?php echo $this->security->get_csrf_hash() ?>" id="csrf_protection" /> 
				<input type="hidden" value="<?=$server_name?>" id="server_name">
				<?=$error_flash?>
				<h1><?=$heading?></h1>
				<div id="shortcuts"><a href="/store/">To Home</a> 
				<?if(isset($shortcuts) && $shortcuts){foreach($shortcuts as $shortcut){
				?><a href="<?=$shortcut['link']?>"><?=$shortcut['name']?></a><?	
				}}?></div>
