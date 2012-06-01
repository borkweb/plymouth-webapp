<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Orders</title>
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
<script src="../js/SpryValidationTextField.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
function MM_setTextOfTextfield(objId,x,newText) { //v9.0
  with (document){ if (getElementById){
    var obj = getElementById(objId);} if (obj) obj.value = newText;
  }
}
function CurrencyFormatted(amount)
{
	if(amount=='')
		return '';
	var i = parseFloat(amount);
	if(isNaN(i)) { i = 0.00; }
	var minus = '';
	if(i < 0) { minus = '-'; }
	i = Math.abs(i);
	i = parseInt((i + .005) * 100);
	i = i / 100;
	s = new String(i);
	if(s.indexOf('.') < 0) { s += '.00'; }
	if(s.indexOf('.') == (s.length - 2)) { s += '0'; }
	s = minus + s;
	return s;
}
//-->
</script>
<link href="../js/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
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
    <form action="update_part_status.php" enctype="multipart/form-data" method="post" id="partform" name="partform">
    <div id="leftcol">
    <fieldset>
     <legend>Parts Needing to Be Ordered</legend>
      <div class="infobox" >
        <table cellpadding="3" cellspacing="0" border="0" width="100%" >
        	<tr class="tablehead">
            	<td>Vendor</td>
                <td>WO #</td>
                <td>Part</td>
                <td align="center">Shipping/Handling</td>
              <td align="center">Ordered <input type="hidden" name="num_ordered" value="{num_order}" /></td>
            </tr>
            <!-- BEGIN: no_order -->
            <tr>
            	<td colspan="5">There are currently no parts waiting to be ordered.</td>
            </tr>
            <!-- END: no_order -->
            <!-- BEGIN: order_part_row -->
            <tr {rowclass} >
            	<td>{vendor}</td>
                <td>{wo}</td>
                <td>{part}</td>
                <td align="center">
                <input name="ship{num}" type="text" size="5" maxlength="14" {disabled} onblur="MM_setTextOfTextfield('ship{num}','',CurrencyFormatted(document.partform.ship{num}.value))" />
                </td>
                <td align="center"><input name="ordered{num}" type="checkbox" value="{id}" {disabled}/></td>
            </tr>
            <!-- END: order_part_row -->
            <!-- BEGIN: order_submit -->
            <tr>
            	<td colspan="4">&nbsp;</td>
                <td align="center"><input name="submit1" type="submit" value="Submit" /></td>
            </tr>
            <!-- END: order_submit -->
        </table>
      </div>
    </fieldset>
    </div>
    <div id="rightcol">
    <fieldset>
    <legend>Parts Ordered, Not Received</legend>
      <div class="infobox" >
         <table cellpadding="3" cellspacing="0" border="0" width="100%" >
        	<tr class="tablehead">
            	<td>Vendor</td>
                <td>WO #</td>
                <td>Part</td>
                <td align="center">Received <input type="hidden" name="num_received" value="{num_receive}" /></td>
            </tr>
            <!-- BEGIN: no_receive -->
            <tr>
            	<td colspan="4">There are currently no parts ordered that have not be received.</td>
            </tr>
            <!-- END: no_receive -->
            <!-- BEGIN: receive_part_row -->
            <tr {rowclass}>
            	<td>{vendor}</td>
                <td>{wo}</td>
                <td>{part}</td>
                <td align="center"><input name="received{num}" type="checkbox" value="{id}" {disabled}/></td>
            </tr>
            <!-- END: receive_part_row -->
            <!-- BEGIN: receive_submit -->
            <tr>
            	<td colspan="3">&nbsp;</td>
                <td align="center"><input name="submit2" type="submit" value="Submit" /></td>
            </tr>
            <!-- END: receive_submit -->
        </table>
      </div>
    </fieldset>
    </div>
    
    <br class="clear" /><br />
    <fieldset> 
    <legend>Parts Received For Currently Open Calls</legend>
      <div class="infobox" >
        <table cellpadding="3" cellspacing="0" border="0" width="100%" >
          <tbody>
            <tr class="tablehead">
              <td>WO #</td>
              <td >Part</td>
              <td>Vendor</td>
              <td >Name</td>
              <td >Manufacturer</td>
              <td >Model</td>
              <td align="center" >Undo <input type="hidden" name="num_undo" value="{num_undo}" /></td>
            </tr>
            <!-- BEGIN: receivedrow -->
            <tr {rowclass}>
              <td>{wo}</td>
              <td >{part}</td>
              <td>{vendor}</td>
              <td >{name}</td>
              <td >{manufacturer}</td>
              <td >{model}</td>
              <td align="center"><input name="undo{num}" type="checkbox" value="{id}" {disabled}/></td>
            </tr>
            <!-- END: receivedrow -->
            <!-- BEGIN: receivednone -->
            <tr>
              <td colspan="7">None found.</td>
            </tr>
            <!-- END: receivednone -->
            <!-- BEGIN: received_submit -->
            <tr>
            	<td colspan="6">&nbsp;</td>
                <td align="center"><input name="submit3" type="submit" value="Submit" /></td>
            </tr>
            <!-- END: received_submit -->
            </tbody>
        </table>
       </div>
     </fieldset>
      </form>
  </div>
 
</div>

</body>
</html>
<!-- END: main -->
