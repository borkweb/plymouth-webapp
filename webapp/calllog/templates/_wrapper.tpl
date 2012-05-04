<!DOCTYPE html>
<html>
<head>
	<title>{$title} | Call Log</title>
	<link rel="stylesheet" type="text/css" href="/webapp/style.css">
</head>
<body>

{PSU_GoBar}

<div id="header">
	<div class="inner">
		<h1>{$title}</h1>
		<div id="nav">
			<ul>
				<li><a href="{$PHP.BASE_URL}">CallLog</a></li>
			</ul>
		</div>
	</div>
</div>

<div id="page">
	{include file="`$content`.tpl"}
</div>

</body>
</html>
