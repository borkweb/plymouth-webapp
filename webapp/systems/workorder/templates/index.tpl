<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Order</title>
<link href="templates/css/thickbox.3.css" rel="stylesheet" type="text/css" media="all" />
<link href="templates/css/style.css" rel="stylesheet" type="text/css" media="all" />
<script src="/includes/js/jquery-latest.pack.js" type="text/javascript"></script>
<script src="js/jquery.thickbox.3.js" type="text/javascript"></script>
<script language="javascript">
function toggleSubmit()
{
	var val=document.agreement.submit.disabled;
	if( val == true){ document.agreement.submit.disabled=false; } else {document.agreement.submit.disabled=true;}
}
function MM_callJS(jsStr) { //v2.0
  return eval(jsStr)
}
function MM_goToURL() { //v3.0
  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
}
</script>
</head>
<body>
<div align="center">
<div id="main" style="margin-top:20px">
  <div id="header">
  	<img src="templates/images/logo2.png" width="223" height="60" />  </div>
  <div id="topbar">
  </div>
  <div id="content" align="center" >
  	<div id="centerfloat">
    	<div class="headertext" align="center">PSU Computer Store Work Order</div>
        <p>
       	The ITS Repair Center makes every attempt to prevent data loss. However, there are occasions when loss cannot be prevented. We strongly encourage you to back up your data on a regular basis and maintain copies of your installation media (this includes restoration CD or DVD, word processing suite, etc.) You must provide any software and/or licensing codes required for reinstallation on your computer. In this event, we are obligated to follow all licensing restrictions and laws.</p>
        <p>All defective/replaced parts will be returned to the owner for proper disposal unless covered under a warranty (warranty replacement requires the return of a defective part). Any equipment not picked up within 60 days of being ready becomes the property of the PSU Repair Center and may be disposed of.</p>
        <p>Warranty hard drive replacement does not include software reinstallation (including operating system), which may incur labor charges. Any hard drive diagnosis, hard drive sector repair, software repair and/or dust removal from the computer are considered non-warranty service and may incur labor charges.</p>
        <p>Please make sure you understand what your warranty covers and know what our labor rates are.  The rates for Faculty, Staff, Alumni, Faculty Emeritus, and PSU Guests can be found <a href="http://www.plymouth.edu/office/information-technology/help/repair/rates-staff/?keepThis=true&TB_iframe=true&height=700&width=900" class="thickbox">here</a>.  Rates for Students can be found <a href="http://www.plymouth.edu/office/information-technology/help/repair/rates-students/?keepThis=true&TB_iframe=true&height=700&width=900" class="thickbox">here</a></p>
        <p align="center"><strong>We Accept Check, Mastercard &amp; Visa For Payment</strong><br /> 
        We do not accept cash at this time</p>
        <div align="center"><form action="workorder.html" method="post" enctype="multipart/form-data" name="agreement">
          <p><input name="accept_terms" type="checkbox" onclick="MM_callJS('toggleSubmit();')" value="1" />
          <label>I have read the disclaimer above and agree to its terms.</label><br /><br />
    <input name="submit" type="submit" disabled value="Agree & Continue" id="submit"  class="buttons"/>
    <input name="disagree" type="button" id="disagree" onclick="MM_goToURL('parent','{logouturl}');return document.MM_returnValue" value="I Do NOT Agree" class="buttons" />
    </p>
  </form>
  	</div>
  	</div>
  </div>
  <div id="bottombar">
  </div>
</div>
</div>
</body>
</html>
<!-- END: main -->
