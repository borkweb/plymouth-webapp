<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Repair Shop Work Order</title>
<link href="templates/css/style.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript">
<!--
function delayedRedirect(){
    //window.location = "{logouturl}"
	window.close();
}

//-->
</script>
</head>
<body {onload}>
<div align="center">
  <div id="main">
    <div id="header"> <img src="templates/images/logo2.png" /> </div>
    <div id="topbar"> </div>
    <div id="content" align="center" >
      <div id="centerfloat">
        <div class="headertext" align="center">Your Work Order Has Been Submitted Succesfully</div>
        <p align="center"> Your work order number is {workorder}.  <!-- BEGIN: logoutmessage -->A confirmation email has been sent to {email}.<br />
        You will be automatically logged out in 15 seconds.  If you're not,
        <a href="javascript:window.close();">click here to log out</a>.<!-- END: logoutmessage -->
        </p>
      </div>
    </div>
  <div id="bottombar"> </div>
</div>
</div>
</body>
</html>
<!-- END: main -->
