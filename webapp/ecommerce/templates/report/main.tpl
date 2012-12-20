<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>{if $selected_processor}{$selected_processor} | {/if}{$title}</title>
	{strip}
	{PSU_CSS href="https://www.plymouth.edu/webapp/style.css"}
	{PSU_CSS href="`$PHP.BASE_URL`/../templates/style.css"}
	{PSU_JS src="/includes/js/jquery-1.2.6.js"}
	{/strip}
	
	<script type="text/javascript">
		$(function(){
			$('.report tr').click(function(){
				$(this).toggleClass('clicked');
			});
		});
	</script>
	
	<!--[if lt IE 7]>
	<script language="JavaScript">
	function correctPNG() // correctly handle PNG transparency in Win IE 5.5 & 6.
	{literal}
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
	{/literal}
	</script>
	<![endif]-->
	
	<style>
	{literal}
		.report th{
			border-bottom: 2px solid #BFBEA3;
			text-align: center;
		}
		
		.report td{
			border-bottom: 1px solid #F3F1E6;
			border-top: 1px solid #F3F1E6;
			padding: 5px 0;
		}

		
		.report tr.clicked{
			background: #fffaaa;
		}
		
		.report tr.clicked td{
			border-color: #BFBEA3;
		}
		
		.report tr:hover{
			background: #fff;
		}
		
		.report tr:hover td{
			border-color: #BFBEA3;
		}
		
		.total td{
			border-bottom: 2px solid #BFBEA3;
			border-top: 3px double #BFBEA3;
			font-size: 1.3em;
			padding: 10px 5px;
		}
		
		.sub-total td{
			border-bottom: 1px solid #BFBEA3;
			border-top: 1px solid #BFBEA3;
			font-size: 1.0em;
			text-align: right;
		}
		
		.total .label{
			color: #AEAD92;
			font-weight: bold;
			text-align: right;
		}
		
		.total .label span{
			color: #333;
		}
		
		.total .value{
			text-align: right;
		}
		
		.money{
			text-align: right;
		}
		
		.info ul{
			font-size: 0.9em;
			margin: 2em 0;
			padding: 0;
			text-align: center;
		}
		
		.info li{
			display: inline-block;
			margin-left: 20px;
		}
		
		.info label{
			font-weight: bold;
		}	
		{/literal}
	</style>
</head>
<body>
<div id="header">
	<div class="inner">
		<h1>
			<a href="{$PHP.BASE_URL}/index.html">PSU E-Commerce Report</a>
		</h1>
		<ul id="nav">
			<li class="first {$home_current}"><a href="{$PHP.BASE_URL}/index.html">Home</a></li>
		</ul>
	</div>
</div>
<div id="page">
	{psu_messages}
	{psu_errors}
	{if $content}
		<div id="content">
		{include file="`$content`"}
		</div> <!-- end content -->
	{/if}
</div> <!-- end page -->
<div id="footer">&nbsp;</div>
</body>
</html>
