<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1">
	<title>{if $webapp_page_title}{$webapp_page_title} - {/if}{if $PAGETITLE}{$PAGETITLE}{else}{$webapp_app_title}{/if}</title>
	{strip}
	{PSU_CSS href="/app/core/css/psu-icons.css"}
	{$webapp_content_css}
	{if $PHP.BASE_URL != 'http://www2.plymouth.edu/webapp/style' 
	 && $PHP.BASE_URL != 'http://www.plymouth.edu/webapp/style' 
	 && $PHP.BASE_URL != 'http://www.dev.plymouth.edu/webapp/style' 
	 && $PHP.USE_APPLICATION_STYLE
	}{PSU_CSS href="`$PHP.BASE_URL`/templates/style.css?v=1"}{/if}
	{PSU_JS src="/js/jquery-plugins/jquery.tablesorter.min.js"}
	{$webapp_content_js}
	{/strip}
	{if $smarty.session.username == 'mtbatchelder'}
    <script>var mattyb = true;</script>
	{else}
		<script>var mattyb = false;</script>
	{/if}
  <!--[if lt IE 9]>
    <script src="/app/core/js/html5shiv.js"></script>
  <![endif]-->
</head>
<!--[if lt IE 7 ]> <body {if $body_id}id="{$body_id}"{/if} class="ie6 {$webapp_content_classes} {$body_style_classes} "> <![endif]--> 
<!--[if IE 7 ]>    <body {if $body_id}id="{$body_id}"{/if} class="ie7 {$webapp_content_classes} {$body_style_classes} "> <![endif]--> 
<!--[if IE 8 ]>    <body {if $body_id}id="{$body_id}"{/if} class="ie8 {$webapp_content_classes} {$body_style_classes} "> <![endif]--> 
<!--[if gt IE 8]><!--> <body {if $body_id}id="{$body_id}"{/if} class="{$webapp_content_classes} {$body_style_classes} webapp-prod"> <!--<![endif]--> 
{if $smarty.session.impersonate}
<div id="impersonating">
	<div class="container_16">
	<div class="grid_16">
		<img src="https://s0.dev.plymouth.edu/images/icons/22x22/status/dialog-warning.png" class="icon"/>
		You are currently impersonating <a href="https://go.plymouth.edu/ape/{$smarty.session.username}" target="_blank">{$smarty.session.username}</a> (<a href="https://go.plymouth.edu/ape/{$smarty.session.wp_id}" target="_blank">{$smarty.session.wp_id}</a>).
		<strong>Anything</strong> you do will be done as {$smarty.session.username} and <em>will</em> be <strong>logged</strong>.
		<a href="/webapp/ape/actions/account-impersonate.php?identifier={$smarty.session.username}&action=cancel">Switch back</a> to your account.
	</div>
	</div>
</div>
{/if}
<div id="page">
<div id="page-inner">
<header id="webapp-head">
	<div class="container">
	<div class="container_16 webapp-head">
		<div class="inner">
			{$webapp_content_head}
			<div class="clear"></div>
		</div>
	</div>
	</div>
</header>
<nav id="webapp-nav">
	<div class="container">
	<div class="container_16 webapp-nav">
		<div class="inner">
			{$webapp_content_nav}
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
	</div>
</nav>
<div id="webapp-avant-body">
	<div class="container">
	<div class="container_16 webapp-avant-body">
		<div class="inner">
			{$webapp_content_avant_body}
			{psu_messages}
			{psu_successes}
			{psu_warnings}
			{psu_errors}
			<div class="clear"></div>
		</div>
	</div>
	</div>
</div>
<div id="webapp-body">
	<div class="container">
	<div class="container_16 webapp-body">
		<div class="inner">
			{$webapp_content_body}
			<div class="clear"></div>
		</div>
	</div>
	</div>
</div>
<div id="webapp-apres-body">
	<div class="container">
	<div class="container_16 webapp-apres-body">
		<div class="inner">
			{$webapp_content_apres_body}
			<div class="clear"></div>
		</div>
	</div>
	</div>
</div>
<div id="webapp-foot">
	<div class="container">
	<div class="container_16 webapp-foot">
		<div class="inner">
			{$webapp_content_foot}
			<div class="clear"></div>
		</div>
	</div>
	</div>
</div>
<div id="webapp-apres-foot">
	<div class="container">
	<div class="container_16 webapp-apres-foot">
		<div class="inner">
			{$webapp_content_apres_foot}
			<div class="clear"></div>
		</div>
	</div>
	</div>
</div>
<footer id="webapp-final">
	<div class="container">
	<div class="container_16 webapp-final">
		<div class="inner">
				<a href="#webapp-page" id="return-to-top">&#8607; Return to top</a>
				<span id="foot-address">17 High Street. Plymouth, New Hampshire 03264-1595</span> | <span id="foot-copy">{$smarty.now|date_format:"%Y"} &copy; Plymouth State University</span>
			<div class="clear"></div>
		</div>
	</div>
	</div>
</footer>
<div class="clear"></div>
</div>
</div>
{if $facebook_enable}
<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US"></script>
<script>FB.init('{$facebook_api.api}');</script>
{/if}
<script>
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-125829-14']);
	_gaq.push(['_trackPageview']);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
</body>
</html>
