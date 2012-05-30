<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
  "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Remote Files{if $title} - {$title|strip_tags}{/if}</title>
	<link rel="stylesheet" type="text/css" href="{$PHP.COMMON_JS}/jquery-plugins/thickbox.3.css">
	<link rel="stylesheet" type="text/css" href="{$PHP.BASE_URL}/css/style.css?v=1">
	<script type="text/javascript">
	var BASE_URL = "{$PHP.BASE_URL|escape:'javascript'}";
	</script>
	<script type="text/javascript" src="{$PHP.BASE_URL}/js/swfupload.min.js?v=1"></script>
	<script type="text/javascript" src="{$PHP.BASE_URL}/js/remotefiles.js?v=1"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
	<script type="text/javascript" src="{$PHP.COMMON_JS}/jquery-plugins/jquery.thickbox.3.js"></script>
</head>

<body>

<div id="header">
	<h1>{$title}</h1>
	<div id="links">
		Welcome, {$SESSION.username}! (<a href="{$PHP.BASE_URL}/logout.php">logout</a>)
	</div>
</div>

{psu_messages}
{psu_errors}

{if $content}
	{include file="`$content`.tpl"}
{/if}

</body>

</html>
