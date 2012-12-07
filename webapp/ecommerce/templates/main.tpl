<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>{$title} | PSU E-Commerce</title>
	{strip}
	{PSU_CSS href="https://www.plymouth.edu/webapp/style.css"}
	{PSU_CSS href="`$PHP.BASE_URL`/templates/style.css"}
	{PSU_JS src="/includes/js/jquery-1.2.6.js"}
	{/strip}
	<!--[if lt IE 7]>
	<script language="javascript" src="/includes/js/correctPNG.js"></script>
	<![endif]-->
</head>
<body>
<div id="header">
	<div class="inner">
		<h1>
			<a href="{$PHP.BASE_URL}/">
			{if $header_title_code == 'flexcash'}
			 Campus FlexCash
			{else}
				PSU E-Commerce
			{/if}
			</a>
		</h1>
		{if !$hide_nav}
		<ul id="nav">
			<li class="first {$home_current}"><a href="{$PHP.BASE_URL}/">Home</a></li>
		</ul>
		{/if}
		<div class="clear"></div>
	</div>
</div>
<div id="page">
	{psu_messages}
	{psu_errors}
	{if $content}
		<div id="content">
		{include file="`$content`"}
		</div>
	{/if}
</div>
<div id="footer">&nbsp;</div>
</body>
</html>
