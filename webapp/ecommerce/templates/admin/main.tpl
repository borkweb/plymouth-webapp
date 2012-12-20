<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html 
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>{$page_title|default:'ECommerce'} | ECommerce</title>
		<link rel="stylesheet" media="screen" type="text/css" href="{$PHP.CSS}"/>
		<link rel="stylesheet" media="screen" type="text/css" href="{$PHP.BASE_URL}/templates/admin/style.css"/>
		<!--[if lt IE 7]>
		<script language="JavaScript">
		function correctPNG() // correctly handle PNG transparency in Win IE 5.5 & 6.
		{
			 var arVersion = navigator.appVersion.split("MSIE")
			 var version = parseFloat(arVersion[1])
			 if ((version >= 5.5) && (document.body.filters)) 
			 {
					for(var i=0; i<document.images.length; i++)
					{
						 var img = document.images[i]
						 var imgName = img.src.toUpperCase()
						 if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
						 {
								var imgID = (img.id) ? "id='" + img.id + "' " : ""
								var imgClass = (img.className) ? "class='" + img.className + "' " : ""
								var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
								var imgStyle = "display:inline-block;" + img.style.cssText 
								if (img.align == "left") imgStyle = "float:left;" + imgStyle
								if (img.align == "right") imgStyle = "float:right;" + imgStyle
								if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
								var strNewHTML = "<span " + imgID + imgClass + imgTitle
								+ " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
								+ "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
								+ "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>" 
								img.outerHTML = strNewHTML
								i = i-1
						 }
					}
			 }    
		}
		window.attachEvent("onload", correctPNG);
		</script>
		<![endif]-->
	</head>
	<body>
		<div id="header">
			<div class="inner">
				<h1><a href="{$PHP.BASE_URL}/admin">Welcome PSU eCommerce Admin</a></h1>
				<ul id="nav">
					<li><a href="{$PHP.BASE_URL}/admin/">Dashboard</a></li>
					<li><a href="{$PHP.BASE_URL}/admin/manage.html">Manage Processor</a></li>
				</ul>
			</div>
		</div>
		<div id="page">	
			{if $content}
				{include file=$content}
			{/if}
			{if $subcontent}
				{include file=$subcontent}
			{/if}
		</div>
	</body>
</html>
