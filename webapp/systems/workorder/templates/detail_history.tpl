<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Orders</title>
<link href="templates/css/thickbox.3.css" rel="stylesheet" type="text/css" media="all" />
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
<script src="/includes/js/jquery-latest.pack.js" type="text/javascript"></script>
<script src="js/jquery.thickbox.3.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
//-->
</script>
</head>
<body class="altbackground">
<div align="center">
  <div id="main">
    <div id="newlink" >
      <a href="javascript:history.back();" >Back</a>&nbsp;&nbsp;&nbsp;
        <a href="admin.html" >Back To Admin</a>
    </div>
    <div id="logoutlink">
      <a href="{logouturl}" >Logout</a>
    </div>
    <br class="clear" />
    <div id="leftcol">
    <fieldset>
     	<legend>Device Information</legend>
      <div class="infobox">
        <table cellpadding="3" cellspacing="0" border="0" width="100%" >
          <tbody>
            <tr>
              <td width="30%"><label>Work Order #:</label></td>
              <td>{wo}</td>
            </tr>
            <tr>
              <td ><label>Device Type:</label></td>
              <td>{device}</td>
            </tr>
            <tr>
              <td ><label>Manufacturer:</label></td>
              <td>{manufacturer}</td>
            </tr>
            <tr>
              <td ><label>Model:</label></td>
              <td>{model}</td>
            </tr>
            <tr>
              <td ><label>Serial #:</label></td>
              <td>{serial}</td>
            </tr>
            <tr>
              <td ><label>PSU Property</label></td>
              <td>{psu_property}</td>
            </tr>
            <!-- BEGIN: passwords -->
            <!-- BEGIN: bios -->
            <tr>
              <td valign="top"><label>BIOS:</label></td>
              <td>{bios_pw}</td>
            </tr>
            <!-- END: bios -->
            <!-- BEGIN: windows -->
            <tr>
              <td valign="top"><label>Windows:</label></td>
              <td>{windows_pw}</td>
            </tr>
            <!-- END: windows -->
            <!-- BEGIN: screen -->
            <tr>
              <td valign="top"><label>Screensaver:</label></td>
              <td>{screen_pw}</td>
            </tr>
            <!-- END: screen -->
            <!-- END: passwords -->
            <tr>
              <td valign="top" ><label>Peripherals:</label></td>
              <td colspan="2" >{periphs}</td>
            </tr>
            <!-- BEGIN: other_periphs -->
            <tr>
              <td valign="top"><label>Other:</label></td>
              <td>{other_periphs}</td>
            </tr>
            <!-- END: other_periphs -->
            <tr>
              <td ><label>Status:</label></td>
              <td>{status}</td>
            </tr>
            <tr>
              <td ><label>Payment:</label></td>
              <td>{payment}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </fieldset>
    </div>
    <div id="rightcol">
    <fieldset>
     <legend>User Information</legend>
      <div class="infobox" >
        <table cellpadding="3" cellspacing="0" border="0" width="100%">
          <tbody>
            <tr>
              <td width="30%"><label>Name:</label></td>
              <td>{name}</td>
            </tr>
            <tr>
              <td ><label>Username:</label></td>
              <td>{username}</td>
            </tr>
            <tr>
              <td ><label>Role:</label></td>
              <td>{role}</td>
            </tr>
            <tr>
              <td ><label>Primary Phone:</label></td>
              <td>{phone_primary}</td>
            </tr>
            <tr>
              <td ><label>Other Phone:</label></td>
              <td>{phone_other}</td>
            </tr>
            <!-- BEGIN: studenthousing -->
            <tr>
              <td ><label>Lives On Campus:</label></td>
              <td>{oncampus}</td>
            </tr>
            <!-- END: studenthousing -->
          </tbody>
        </table>
      </div>
    </fieldset>
    <br />
    <fieldset>
    	<legend>Problem Description</legend>
      <div class="infobox"> {problem} </div>
    </fieldset>
    </div>
    <br class="clear" />
    <!-- BEGIN: workperformed -->
    <fieldset>
     <legend>Work Performed</legend>
      <div class="infobox" >
        <table cellpadding="3" cellspacing="0" border="0" width="100%" >
          <tbody>
            <tr class="tablehead">
              <td><strong>Description</strong></td>
              <td ><div align="center"><strong>Labor</strong></div></td>
              <td><div align="right"><strong>Total Charged</strong></div></td>
              <td ><div align="center"><strong>Entered By</strong></div></td>
              <td ><div align="center"><strong>Time Entered</strong></div></td>
              <td ><div align="center"><strong>Removed By</strong></div></td>
              <td ><div align="center"><strong>Time Removed</strong></div></td>
            </tr>
            <!-- BEGIN: itemrow -->
            <tr {rowclass}>
              <td>{item}</td>
              <td ><div align="center">{labor} hrs</div></td>
              <td><div align="right">${cost}</div></td>
              <td ><div align="center">{itemusername}</div></td>
              <td ><div align="center">{entered}</div></td>
              <td ><div align="center">{remusername}</div></td>
              <td ><div align="center">{removed}</div></td>
            </tr>
            <!-- END: itemrow -->
            </tbody>
        </table>
        <br />
         <table cellpadding="3" cellspacing="0" border="0" width="100%" >
          <tbody>
            <tr class="tablehead">
              <td><strong>Part</strong></td>
              <td><strong>Vendor</strong></td>
              <td><div align="right"><strong>Our Cost</strong></div></td>
              <td><div align="right"><strong>Shipping</strong></div></td>
              <td><div align="right"><strong>Cust Cost</strong></div></td>
              <td><div align="right"><strong>Total Charged</strong></div></td>
              <td ><div align="center"><strong>Entered By</strong></div></td>
              <td ><div align="center"><strong>Time Entered</strong></div></td>
              <td ><div align="center"><strong>Removed By</strong></div></td>
              <td ><div align="center"><strong>Time Removed</strong></div></td>
            </tr>
            <!-- BEGIN: partrow -->
            <tr {rowclass} >
              <td>{item}</td>
              <td>{vendor}</td>
              <td><div align="right">${ourcost}</div></td>
              <td><div align="right">${shipping}</div></td>
              <td><div align="right">${custcost}</div></td>
              <td><div align="right">${cost}</div></td>
              <td ><div align="center">{itemusername}</div></td>
              <td ><div align="center">{entered}</div></td>
              <td ><div align="center">{remusername}</div></td>
              <td ><div align="center">{removed}</div></td>
            </tr>
            <!-- END: partrow -->
          </tbody>
        </table>
      </div>
    </fieldset>
    <!-- END: workperformed -->
    <fieldset>
     <legend>Status</legend>
      <div class="infobox" >
      	<table cellpadding="3" cellspacing="0" border="0" width="100%" >
          <tbody>
            <tr class="tablehead">
              <td><strong>Status</strong></td>
              <td valign="top" ><div align="center"><strong>Changed By</strong></div></td>
              <td valign="top" ><div align="center"><strong>Time Changed</strong></div></td>
            </tr>
            <!-- BEGIN: statusrow -->
            <tr {rowclass}>
              <td>{status}</td>
              <td ><div align="center">{statususername}</div></td>
              <td ><div align="center">{changed}</div></td>
            </tr>
            <!-- END: statusrow -->
          </tbody>
        </table>
       </div>
    </fieldset>
    <!-- BEGIN: usernotes -->
    <fieldset>
    	<legend>Notes To User</legend>
      <div class="infobox"> 
      <table cellpadding="3" cellspacing="0" border="0" width="100%" >
          <tbody>
            <tr class="tablehead">
              <td><strong>Note</strong></td>
              <td valign="top" ><div align="center"><strong>Entered By</strong></div></td>
              <td valign="top" ><div align="center"><strong>Time Entered</strong></div></td>
            </tr>
            <!-- BEGIN: usernoterow -->
            <tr {rowclass}>
              <td>{comments}</td>
              <td ><div align="center">{commentusername}</div></td>
              <td ><div align="center">{entered}</div></td>
            </tr>
            <!-- END: usernoterow -->
          </tbody>
        </table>
      </div>
    </fieldset>
    <!-- END: usernotes -->
    <!-- BEGIN: technotes -->
    <fieldset>
    	<legend>Tech Notes</legend>
      <div class="infobox">
      <table cellpadding="3" cellspacing="0" border="0" width="100%" >
          <tbody>
            <tr class="tablehead">
              <td><strong>Note</strong></td>
              <td valign="top" ><div align="center"><strong>Entered By</strong></div></td>
              <td valign="top" ><div align="center"><strong>Time Entered</strong></div></td>
            </tr>
            <!-- BEGIN: technoterow -->
            <tr {rowclass}>
              <td>{note}</td>
              <td ><div align="center">{noteusername}</div></td>
              <td ><div align="center">{entered}</div></td>
            </tr>
            <!-- END: technoterow -->
          </tbody>
        </table>
      </div>
    </fieldset>
    <!-- END: technotes -->
  </div>
</div>
</body>
</html>
<!-- END: main -->
