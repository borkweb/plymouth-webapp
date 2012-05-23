<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Repair Shop Work Order</title>
<link href="templates/css/thickbox.3.css" rel="stylesheet" type="text/css" media="all" />
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
<script src="js/SpryValidationTextField.js" type="text/javascript"></script>
<script src="/includes/js/jquery-latest.pack.js" type="text/javascript"></script>
<script src="js/jquery.thickbox.3.js" type="text/javascript"></script>
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
function CheckEnablePayment()
{	
	var index = document.update.status.selectedIndex;
	var radioLength = document.update.payment.length;
	if(document.update.status.options[index].text=="Close: returned to user" && document.update.total_due.value>0)
	{	
		for(var i = 0; i < radioLength; i++) {
			document.update.payment[i].disabled=false;
		}
	}
	else
	{
		for(var i = 0; i < radioLength; i++) {
			document.update.payment[i].checked=false;
			document.update.payment[i].disabled=true;
		}
	}
}
function EnablePropertyChecks()
{
	document.update.psuproperty[0].disabled=false;
	document.update.psuproperty[1].disabled=false;
	propertyeditlink.style.display="none";
}
function EnableSerial()
{
	document.update.serial.disabled=false;
	document.update.serial.style.display="";
	serialeditlink.style.display="none";
}
function EnableManufacturer()
{
	document.update.manufacturer.disabled=false;
	document.update.manufacturer.style.display="";
	manufacturereditlink.style.display="none";
}
function EnableModel()
{
	document.update.model.disabled=false;
	document.update.model.style.display="";
	modeleditlink.style.display="none";
}
function CloseCheck(){
	var index = document.update.status.selectedIndex;
	var radioLength = document.update.payment.length;
	if(document.update.status.options[index].text=="Close: returned to user")
	{
		if({open_parts}==1)
		{
			alert("Error: Still waiting for receipt of parts for this workorder.\nYou must mark all parts as received before closing.");
			return false;
		}
		else if(document.update.total_due.value>0 && getCheckedValue(document.update.payment)==false)
		{
			alert("You must choose a payment method before closing this call");
			return false;
		}
	}
	else
		return true;
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return false;
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return false;
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return false;
}
//-->
</script>
<link href="js/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
</head>
<body class="altbackground" onload="{onload}">
<div align="center">
  <div id="main">
    <div id="newlink" >
        <a href="javascript:history.back();" >Back</a>&nbsp;&nbsp;&nbsp;
        <a href="admin.html" >Back To Admin</a>&nbsp;&nbsp;&nbsp;<a href="parts_request.html?id={wo}&psu={psu_property}&keepThis=true&TB_iframe=true&height=600&width=900" class="thickbox">Order Parts</a>
    </div>
    <div id="logoutlink">
      <a href="{logouturl}" >Logout</a>
    </div>
    <br class="clear" />
    <!-- BEGIN: invoiceheader -->
    <p id="invoiceheader" ><strong>Plymouth State University Repair Shop</strong><br />
      MSC #23<br />
      17 High Street<br />
      Plymouth, NH 03264
      (603)535-3499<br />
      <br />
      {date} </p>
    <!-- END: invoiceheader -->
    <br  />
    <form action="{formaction}" id="update" name="update" enctype="multipart/form-data" method="post" onsubmit="return CloseCheck();">
      <div id="leftcol">
        <fieldset>
        	<legend>Device Information</legend>
          <div class="infobox">
            <table cellpadding="3" cellspacing="0" border="0" width="100%" >
              <tbody>
                <tr>
                  <td width="30%"><label>Work Order #:</label></td>
                  <td><a href="detail_history.html?id={wo}" target="_top">{wo}</a>
                    <input name="wo" type="hidden" value="{wo}" /></td>
                </tr>
                <tr>
                  <td ><label>Submitted:</label></td>
                  <td>{submitted}</td>
                </tr>
                <tr>
                  <td ><label>Device Type:</label></td>
                  <td>{device}</td>
                </tr>
                <tr>
                  <td ><label>Manufacturer:</label></td>
                  <td>{manufacturer} &nbsp;&nbsp;
                  <!-- BEGIN: manufactureredit --> <input name="manufacturer" type="text" disabled value="{manufacturer}" size="20" maxlength="50" style="font-size:10px;display:none" /> &nbsp;<span id="manufacturereditlink"><a href="javascript:EnableManufacturer()">Edit</a></span>
                  <!-- END: manufactureredit -->
                  </td>
                </tr>
                <tr>
                  <td ><label>Model:</label></td>
                  <td>{model} &nbsp;&nbsp;
                  <!-- BEGIN: modeledit --> <input name="model" type="text" disabled value="{model}" size="20" maxlength="50" style="font-size:10px;display:none" /> &nbsp;<span id="modeleditlink"><a href="javascript:EnableModel()">Edit</a></span>
                  <!-- END: modeledit -->
                  </td>
                </tr>
                <tr>
                  <td ><label>Serial/Service Tag:</label></td>
                  <td>{serial}&nbsp;&nbsp;
                  <!-- BEGIN: serialedit --> <input name="serial" type="text" disabled value="{cleanserial}" size="20" maxlength="30" style="font-size:10px;display:none" /> &nbsp;<span id="serialeditlink"><a href="javascript:EnableSerial()">Edit</a></span>
                  <!-- END: serialedit -->
                  </td>
                </tr>
                <!-- BEGIN: dellwarranty -->
                <tr>
                  <td valign="top"><label>Ship Date:</label></td>
                  <td>{dell_ship_date}</td>
                </tr>
                <tr>
                  <td valign="top"><label>Warranty End:</label></td>
                  <td>{dell_warranty_end}</td>
                </tr>
                <!-- END: dellwarranty -->
                <tr>
                  <td ><label>PSU Property</label></td>
                  <td>Yes <input name="psuproperty" type="radio" disabled {ppyescheck} value="1" />  &nbsp;&nbsp;No <input name="psuproperty" type="radio" disabled {ppnocheck} value="0" />  
                  <!-- BEGIN: propertyedit -->
                 	<span id="propertyeditlink"> <a href="javascript:EnablePropertyChecks()">Edit</a></span>
                  <!-- END: propertyedit -->
                  </td>
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
                  <td >{periphs}</td>
                </tr>
                <!-- BEGIN: other_periphs -->
                <tr>
                  <td valign="top"><label>Other:</label></td>
                  <td>{other_periphs}</td>
                </tr>
                <!-- END: other_periphs -->
                <!-- BEGIN: tech_assigned -->
                <tr>
                  <td ><label>Tech Assigned:</label></td>
                  <td>
                  		<select name="tech" size="1" >
                        <option value=""  >Unassigned</option>
                      <!-- BEGIN: techentry -->
                      <option {techselect}>{tech}</option>
                      <!-- END: techentry -->
                    </select>
                  </td>
                </tr>
                <!-- END: tech_assigned -->
                <!-- BEGIN: status -->
                <tr>
                  <td ><label>Status:</label></td>
                  <td><select name="status" size="1" {statusdisabled}  onchange="CheckEnablePayment()">
                      <!-- BEGIN: statusentry -->
                      <option {statusselect}  >{statusoption}</option>
                      <!-- END: statusentry -->
                    </select></td>
                </tr>
                <tr>
                  <td valign="top" ><label>Payment Method:</label></td>
                  <td><input name="payment" type="radio" value="check" disabled /><label>Check</label>&nbsp;&nbsp;&nbsp;<input name="payment" type="radio" value="credit" disabled /><label>Credit Card</label>&nbsp;&nbsp;&nbsp;<input name="payment" type="radio" value="foapal" disabled /><label>Foapal</label></td>
                </tr>
                <!-- END: status -->
                
              </tbody>
            </table>
          </div>
        </fieldset>
        <br />
        <fieldset>
         <legend>Problem Description</legend>
          <div class="infobox">
            <!-- BEGIN: problemdisplay -->
            {problem}
            <!-- END: problemdisplay -->
          </div>
        </fieldset>
        <br />
        <fieldset>
         <legend>Notes To User</legend>
          <div class="infobox">
            <!-- BEGIN: commentsedit -->
            <div align="center">
              <textarea name="comments" cols="55" rows="6" wrap="virtual">{comments}</textarea>
            </div>
            <!-- END: commentsedit -->
            <!-- BEGIN: commentsdisplay -->
            {comments}
            <!-- END: commentsdisplay -->
          </div>
        </fieldset>
        <br />
        <!-- BEGIN: technotes -->
        <fieldset>
        	<legend>Tech Notes</legend>
          <div class="infobox">
            <!-- BEGIN: technotesedit -->
            <div align="center">
              <textarea name="notes" cols="55" rows="6" wrap="virtual">{notes}</textarea>
            </div>
            <!-- END: technotesedit -->
            <!-- BEGIN: technotesdisplay -->
            {notes}
            <!-- END: technotesdisplay -->
          </div>
        </fieldset>
        <!-- END: technotes -->
      </div>
      <div id="rightcol">
        <fieldset>
         	<legend>User Information</legend>
          <div class="infobox">
            <table cellpadding="3" cellspacing="0" border="0" width="100%">
              <tbody>
                <tr>
                  <td width="120"><label>Name:</label></td>
                  <td valign="bottom" ><a href="history_list.html?u={username}" >{name}</a> &nbsp;&nbsp;<a href="mailto:{username}@mail.plymouth.edu" style="border:none"><img src="templates/images/mail.gif" border="0" /></a></td>
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
          <div align="right"><span class="headertext"><br />
            Total Due: ${totaldue}<input name="total_due" type="hidden" value="{totaldue}" /></span><br  />
          </div>
        <br   />
        <fieldset>
        	<legend>Work Performed</legend>
          <div class="infobox">
            <table cellpadding="3" cellspacing="0" border="0" width="100%" >
              <tbody>
              <!-- BEGIN: currentitems -->
                <tr class="tablehead" >
                  <td><strong>Description</strong></td>
                  <!-- BEGIN: removecolumn -->
                  <td><div align="center"><strong>Remove</strong></div></td>
                  <!-- END: removecolumn -->
                  <td><div align="right"><strong>Cost</strong></div></td>
                </tr>
              <!-- BEGIN: itemrow -->
                <tr {rowclass}>
                  <td {partclass}>{item}</td>
                  <!-- BEGIN: itemremove -->
                  <td><div align="center">
                      <input name="remove{removenum}" type="checkbox" value="{itemid}" />
                    </div></td>
                  <!-- END: itemremove -->
                  <td><div align="right">${cost}</div></td>
                </tr>
              <!-- END: itemrow -->
            <!-- END: currentitems -->
                <!-- BEGIN: itementry -->
                <tr class="tablehead" >
                  <td ><strong>Description</strong></td>
                  <td><div align="center"><strong>Labor</strong></div></td>
                  <td><div align="right"><strong>Cost</strong></div></td>
                </tr>
                <!-- BEGIN: itementryrow -->
                <tr>
                  <td><input name="item{num}" type="text" size="43" /></td>
                  <td><span id="laborfield{num}"><input name="labor{num}" type="text" size="2" />hrs</span></td>
                  <td><div align="right"> <span id="costfield{num}">
                      <label>$</label>
                      <input name="cost{num}" type="text" size="6" maxlength="6" onblur="MM_setTextOfTextfield('cost{num}','',CurrencyFormatted(document.update.cost{num}.value))"/>
                      <span class="textfieldInvalidFormatMsg"></span></span></div></td>
                </tr>
                <!-- END: itementryrow -->
                <!-- END: itementry -->
              </tbody>
            </table>
            
          </div>
          <br />
          <div id="legend" align="center"><span class="green"><strong>Green</strong></span>=Part Received &nbsp; <span class="yellow"><strong>Orange</strong></span>=Part Ordered &nbsp; <span class="red"><strong>Red</strong></span>=Part Not Yet Ordered</div>
          
          </fieldset>
          <br />
          <!-- BEGIN: editbuttons -->
          <div id="editbuttons" align="center">
            <input type="hidden" name="totalitems" value="{total_items}" />
            <input name="submit" type="submit" value="Update Workorder"/>
            <input name="reset" type="reset" value="Reset Form" />
            <input name="cancel" type="button" onclick="MM_goToURL('parent','admin.html');return document.MM_returnValue" value="Cancel" />
          </div>
          <!-- END: editbuttons -->
       </div>
    </form>
  </div>
</div>
<script type="text/javascript">
<!--
<!-- BEGIN: costchecking -->
var sprytextfield{num} = new Spry.Widget.ValidationTextField("costfield{num}", "currency", {isRequired:false});
var laborsprytextfield{num} = new Spry.Widget.ValidationTextField("labor{num}", "real", {isRequired:false, minValue:0, maxValue:100});
<!-- END: costchecking -->
//-->
</script>
</body>
</html>
<!-- END: main -->
