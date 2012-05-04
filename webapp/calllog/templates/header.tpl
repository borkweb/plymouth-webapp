<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{application_name}</title>

<link rel="stylesheet" type="text/css" href="{CSS_WEB_DIR}/calllog.css?v=4" />
<link rel="stylesheet" type="text/css" href="{CSS_WEB_DIR}/default/default.css?v=2" />
<link rel="stylesheet" type="text/css" href="/app/core/js/jquery-plugins/colorbox/colorbox.css" />

<link rel="stylesheet" type="text/css" href="{CSS_WEB_DIR}/print.css" media="print" />

<script src="/app/core/js/prototype.js" type="text/javascript"></script>
<script src="/app/core/js/scriptaculous/scriptaculous.js" type="text/javascript"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
var $jQuery = jQuery.noConflict();
$jQuery(function(){
$jQuery('#its-group-help').colorbox();
});
</script>

<script type="text/javascript" src="/app/core/js/jquery-plugins/colorbox/jquery.colorbox.js"></script> 
<script type="text/javascript" src="/app/core/js/jquery-plugins/jquery.tablesorter.js"></script> 
<script type="text/javascript" src="/app/core/js/jquery-plugins/jquery.tablesorter.pager.js"></script> 

<script src="{JS_WEB_DIR}/jsr_class.js" type="text/javascript" language="JavaScript"></script>
<script src="{JS_WEB_DIR}/main.js?v=3" type="text/javascript" language="JavaScript"></script>

</head>

<body>

<!-- BEGIN: header -->
<div id="outer-header">
<div id="header-left-side" class="header-left header-div">{tlc_position}: {tlc_full_name}</div>
<div id="header-right-side" class="header-right header-div"><ul id="tlc_nav_list">{top_nav}</ul></div>
</div>
 <!-- BEGIN: user_message -->
  <br style="clear: left;"/><div id="header-user-message">{user_message}</div>
 <!-- END: user_message -->
<!-- END: header -->
<hr noshade="noshade" width="98%" style="clear: left;"/>
<!-- END: main -->
