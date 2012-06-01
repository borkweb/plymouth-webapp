<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Repair Shop Work Orders</title>
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
<script src="js/SpryValidationTextField.js" type="text/javascript"></script>
<link href="js/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
</head>
<body>
<br />
<br />
<br />
	<div align="center">
    <!-- BEGIN: error -->
    <h3 style="color:#FF0000">Username Not Found</h3>
    <!-- END: error -->
    	<form id="user" enctype="multipart/form-data" action="new_call.html" method="post">
     		<label>Username</label>
	    <span id="sprytextfield1">
     		<input type="text" id="username" name="username" size="30" maxlength="35" />
     		</span>&nbsp;&nbsp;
     		<input name="submit" type="submit" value="Open Work Order" />   
        </form><br />
        - For cluster computers, use clusteradm as the username -<br /> 
        - For surplus computers, use computer-surplus as the username -
    </div>
    <script type="text/javascript">
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
//-->
    </script>
</body>
</html>
<!-- END: main -->
