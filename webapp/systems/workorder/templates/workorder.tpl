<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Repair Shop Work Order</title>
<link href="templates/css/style.css" rel="stylesheet" type="text/css" media="all" />
<script src="js/SpryValidationTextField.js" type="text/javascript"></script>
<script src="js/SpryValidationTextarea.js" type="text/javascript"></script>
<link href="js/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script language="javascript">
function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}
function setPeripherals()
{
	var val=getCheckedValue(document.workorder.type);
	if( val == 'laptop' || val=='desktop'){ 
		document.workorder.monitor.disabled=false;
		document.workorder.keyboard.disabled=false;
		document.workorder.mouse.disabled=false;
		document.workorder.printer.disabled=false;
		document.workorder.scanner.disabled=false;
	}
	else if( val == 'printer' || val=='scanner') {
		document.workorder.monitor.disabled=true;
		document.workorder.keyboard.disabled=true;
		document.workorder.mouse.disabled=true;
		document.workorder.printer.disabled=true;
		document.workorder.scanner.disabled=true;
		document.workorder.monitor.checked=false;
		document.workorder.keyboard.checked=false;
	}
	if( val == 'printer' ){ document.workorder.printer.checked=true; document.workorder.scanner.checked=false; }
	if( val == 'scanner' ){ document.workorder.scanner.checked=true; document.workorder.printer.checked=false;}
}
function MM_callJS(jsStr) { //v2.0
  return eval(jsStr)
}
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
function uppercase()
{
  key = window.event.keyCode;
  if ((key > 0x60) && (key < 0x7B))
    window.event.keyCode = key-0x20;
}

</script>
<link href="js/SpryValidationTextarea.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div align="center">
  <div id="main">
    <div id="header"> <img src="templates/images/logo2.png" width="223" height="60" /> </div>
    <div id="topbar"> </div>
    <div id="content"  align="center">
      <div id="centerfloat">
        <form action="process_workorder.php" method="post" enctype="multipart/form-data" name="workorder">
          <table cellpadding="2" cellspacing="0" width="100%" align="center">
            <tbody>
              <tr >
                <td align="left" class="headertext" colspan="2"><p>Personal Information:</p></td>
              </tr>
              <tr >
                <td width="18%"><label>Name: </label></td>
                <td>{name}
                  <input type="hidden" name="name" value="{name}" /><input type="hidden" name="username" value="{user}" /></td>
              </tr>
              <tr >
                <td><label>Role: </label></td>
                <td>{role}
                  <input type="hidden" name="role" value="{role}" />
                  <!-- BEGIN: personal -->
                  <input type="hidden" name="property_type" value="personal" />
                  <input type="hidden" name="policy_accepted" value="1" />
                  <input type="hidden" name="send_email" value="1" />
                  <!-- END: personal --> 
                </td>
                </tr>
              <tr >
                <td ><label>Primary Phone</label></td>
                <td><span id="primaryphonetextfield">
                <input type="text" name="phone_primary" value="{phone_primary}" size="40" maxlength="35" />
                <span class="textfieldRequiredMsg"><em>required</em></span></span></td>
              </tr>
              <tr >
                <td width="15%"><label>Other Phone </label></td>
                <td>
                  <input type="text" name="phone_other" value="{phone_other}" size="40" maxlength="35" />
                 </td>
              </tr>
              </tr>
              <!-- BEGIN: student -->
              <tr>
                <td colspan="2"><label>Housing</label>
                  <input name="housing" type="radio" value="on-campus" checked />
                  <label>On Campus</label>
                  &nbsp;
                  </label>
                  <input name="housing" type="radio" value="off-campus"  />
                  <label>Off Campus</label>
                </td>
              </tr>
              <!-- END: student -->
              
              <tr>
                <td class="headertext" colspan="2">
                  <p>Device Information:</p></td>
              </tr>
               <!-- BEGIN: psu -->
                <tr>
                  <td colspan="2">
                  <label>PSU Property: </label>Yes <input name="property_type" type="radio" value="university" checked /> No <input name="property_type" type="radio" value="personal" />
                  <input type="hidden" name="policy_accepted" value="0" />
                  </td>
                </tr>
                <tr>
                  <td colspan="2">
                  <label>Email user with status updates? </label>Yes <input name="send_email" type="radio" value="1" checked /> No <input name="send_email" type="radio" value="0" />
                  </td>
                </tr>
                  <!-- END: psu -->
              <tr>
                <td colspan="2">
                  <input name="type" type="radio" onclick="MM_callJS('setPeripherals();')" value="laptop" checked />
                  <label>Laptop</label>
                  <input name="type" type="radio" value="desktop" onclick="MM_callJS('setPeripherals();')" /><label>Desktop</label>
                  <input name="type" type="radio" value="printer" onclick="MM_callJS('setPeripherals();')" /><label>Printer</label>
                  <input name="type" type="radio" value="scanner" onclick="MM_callJS('setPeripherals();')" /><label>Scanner</label>
                  <input name="type" type="radio" value="misc" onclick="MM_callJS('setPeripherals();')" /><label>Miscellaneous</label>
                  
                </td>
              </tr>
              <tr>
                <td><label>Manufacturer</label></td>
                <td><span id="manufacturertextfield">
                  <input name="manufacturer" type="text" size="40" maxlength="50" />
                <span class="textfieldRequiredMsg"><em>required</em></span></span></td>
              </tr>
              <tr>
                <td><label>Model</label></td>
                <td><span id="modeltextfield">
                  <input name="model" type="text" size="40" maxlength="50" />
                <span class="textfieldRequiredMsg"><em>required</em></span></span></td>
              </tr>
              <tr>
                <td><label>Serial/Service Tag</label></td>
                <td><span id="serialtextfield">
                  <input name="serial" type="text" size="40" maxlength="30" onKeypress="uppercase();"/>
                  <span class="textfieldRequiredMsg"><em>required</em></span></span>
                </td>
              </tr>
              <tr>
                <td valign="top"><label>Peripherals </label></td>
                <td >
                  <input name="monitor" type="checkbox" value="1" /><label>Monitor</label>
                  <input name="keyboard" type="checkbox" value="1" /><label>Keyboard</label>
                  <input name="mouse" type="checkbox" value="1" /><label>Mouse</label>
                  <input name="ac_adapter" type="checkbox" value="1" /><label>AC Adapter</label>
				  <input name="printer" type="checkbox" value="1" /><label>Printer</label><br />
                  <input name="scanner" type="checkbox" value="1" /><label>Scanner</label>
                  <input name="cable" type="checkbox" value="1" /><label>Printer or Scanner Cable</label>
                  <input name="software" type="checkbox" value="1" /><label>Software</label><br  />
                </td>
              </tr>
              <tr>
                <td valign="top"><label>Other Peripherals</label></td>
                <td>
                  <textarea name="other" cols="50" rows="3" wrap="virtual"></textarea>
                </td>
              </tr>
              <tr>
                <td class="headertext" colspan="2"><br />
                <p>Passwords:</p></td>
              </tr>
              <tr>
                <td><label>Login</label></td>
                <td>
                  <input name="pw_windows" type="password" size="40" maxlength="35" />
                </td>
              </tr>
              <tr>
                <td><label>Screensaver</label></td>
                <td>
                  <input name="pw_screensaver" type="password" size="40" maxlength="35" />
                </td>
              </tr>
              <!--
              <tr>
                <td><label>BIOS/Boot</label></td>
                <td>
                  <input name="pw_system" type="password" size="40" maxlength="35" />
                </td>
              </tr>
              	-->
              <tr>
                <td class="headertext" colspan="2"><br />
                <p>Description Of The Problem:</p></td>
              </tr>
              <tr>
                <td colspan="2" valign="middle"><span id="problemtextarea">
                  <textarea name="problem" cols="70" rows="6" wrap="virtual"></textarea>
                <span class="textareaRequiredMsg"><em>required</em></span></span></td>
              </tr>
              <tr>
              	<td colspan="2" align="center">
                	<input name="submit" type="submit" value="Submit Workorder" id="submit" />
                    <input name="reset" type="reset" value="Reset Form" />
          <input name="cancel" type="button" id="cancel" onclick="MM_goToURL('parent','{logouturl}');return document.MM_returnValue" value="Cancel &amp; Logout" />
                </td>
              </tr>
            </tbody>
          </table>
          
        </form>
      </div>
    </div>
    <div id="bottombar"> </div>
  </div>
</div>
<script type="text/javascript">
<!--
var sprytextfield1 = new Spry.Widget.ValidationTextField("manufacturertextfield");
var sprytextfield2 = new Spry.Widget.ValidationTextField("modeltextfield");
var sprytextfield3 = new Spry.Widget.ValidationTextField("primaryphonetextfield", "none");
var sprytextfield4 = new Spry.Widget.ValidationTextField("serialtextfield");
var sprytextarea1 = new Spry.Widget.ValidationTextarea("problemtextarea");
//-->
</script>
</body>
</html>
<!-- END: main -->
