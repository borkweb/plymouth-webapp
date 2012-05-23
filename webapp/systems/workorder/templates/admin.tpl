<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Repair Shop Work Orders Admin</title>
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
<link href="js/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
</head>
<body class="altbackground">
<div align="center">
  <div id="main">
    <div id="newlink" ><a href="new_call.html?keepThis=true&TB_iframe=true&height=600&width=850" class="thickbox">New Work Order</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="parts_status.html">Parts Status</a></div>
    <div id="logoutlink"> <a href="{logouturl}" >Logout</a> </div>
    <br class="clear" />
    <fieldset>
    	<legend>Active Work Orders</legend>
      <iframe id="activeiframe" src="active_workorders.html" frameborder="0"> </iframe>
    </fieldset>
    <div id="stats">
      <table width="100%" cellspacing="0" cellpadding="3">
        <tbody>
          <tr>
            <td align="center">Calls Open: {open}</td>
            <td align="center">Calls Closed Today: {closed_today}</td>
            <td align="center">Calls Closed This Week: {closed_week}</td>
            <td align="center">Calls Closed This Year: {closed_year}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <br class="clear" />
    <div id="adminleftcol">
      <fieldset>
       <legend>Historical Lookup</legend>
       <div class="infobox">
        <form id="historicform" enctype="multipart/form-data" action="history_list.html" method="post">
          <table cellpadding="3" cellspacing="0" border="0" width="100%">
            <tbody>
              <tr>
                <td><label>Work Order #</label></td>
                <td>
                  <input type="text" name="wo" id="wo"  size="15" maxlength="15"/>
                </td>
              </tr>
              <tr>
                <td><label>Username</label></td>
                <td><input type="text" name="username" id="username" size="40" maxlength="35" />
                </td>
              </tr>
              <tr>
                <td><label>Keyword</label></td>
                <td><input type="text" name="keyword" id="keyword"  size="40" />
                </td>
              </tr>
              <tr>
                <td colspan="2"><label>Work Orders Opened In </label>
                  <select name="month" size="1">
                    <option value="" selected="selected"></option>
                    <option value="1">Jan</option>
                    <option value="2">Feb</option>
                    <option value="3">Mar</option>
                    <option value="4">Apr</option>
                    <option value="5">May</option>
                    <option value="6">Jun</option>
                    <option value="7">Jul</option>
                    <option value="8">Aug</option>
                    <option value="9">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                  </select>
                  &nbsp;
                  <select name="year" size="1">
                    <option value="" selected="selected"></option>
                    <!-- BEGIN: year -->
                    <option value="{year}">{year}</option>
                    <!-- END: year -->
                  </select>
                </td>
              </tr>
              <tr>
                <td colspan="2"><div align="center"><br />
                    <input name="submit" type="submit" value="Search" />
                    <input name="reset" type="reset" value="Reset" />
                  </div></td>
              </tr>
            </tbody>
          </table>
        </form>
        </div>
      </fieldset>
      <br />
      <!-- BEGIN: financial -->
      <fieldset>
       <legend>Financial Reports</legend>
       <div class="infobox">
        <form id="financialform" enctype="multipart/form-data" action="financial_report.html" method="post">
          <table cellpadding="3" cellspacing="0" border="0" width="100%">
            <tbody>
            	<!-- BEGIN: dateerror -->
            	<tr>
                	<td class="red" colspan="2" align="center"><h4>The date(s) you entered were invalid</h4></td>
                </tr>
                <!-- END: dateerror -->
              <tr>
                <td><label>Start Date</label></td>
                <td >
                <select name="month" size="1">
        		<!-- BEGIN: beginmonthoption -->
                    <option value="{month}" {monthselected}>{monthname}</option>
                <!-- END: beginmonthoption -->    
                  </select>&nbsp;
                  <select name="day" size="1">
                    <!-- BEGIN: begindayoption -->
                    <option value="{day}" {dayselected} >{day}</option>
                    <!-- END: begindayoption -->
                  </select>&nbsp;
                  <select name="year" size="1">
                    <!-- BEGIN: year -->
                    <option value="{year}" {yearselected} >{year}</option>
                    <!-- END: year -->
                  </select>
                </td>
              </tr>
              <tr>
                <td><label>End Date</label></td>
                <td >
                <select name="endmonth" size="1">
                    <option value="" ></option>
                    <!-- BEGIN: endmonthoption -->
                    <option value="{month}" {monthselected}>{monthname}</option>
                <!-- END: endmonthoption -->
                  </select>&nbsp;
                  <select name="endday" size="1">
                    <option value="" ></option>
                    <!-- BEGIN: enddayoption -->
                    <option value="{day}" {dayselected} >{day}</option>
                    <!-- END: enddayoption -->
                  </select>&nbsp;
                  <select name="endyear" size="1">
                    <option value="" ></option>
                    <!-- BEGIN: endyear -->
                    <option value="{year}" {yearselected}>{year}</option>
                    <!-- END: endyear -->
                  </select>
                </td>
              </tr>
              <tr>
                <td colspan="2"><div align="center">
                    <em>Leave end date blank to get a detailed breakdown for specified start date</em>
                  </div></td>
              </tr>
              <tr>
                <td colspan="2"><div align="center"><br />
                    <input name="submit" type="submit" value="Report" />
                    <input name="reset" type="reset" value="Reset" />
                  </div></td>
              </tr>
            </tbody>
          </table>
        </form>
      </div>
      </fieldset>
       <!-- END: financial -->
    </div>
     
    <div id="adminrightcol">
    <fieldset>
      <legend>Ready For Pickup</legend>
        <iframe id="pickupiframe" src="ready_pickup.html" frameborder="0"> </iframe>
   </fieldset>
   <br />
   <!-- BEGIN: reimbursement -->
   <fieldset>
       <legend>Warranty Reimbursement Checks</legend>
       <div class="infobox">
        <form id="reimbursementform" name="reimbursementform" enctype="multipart/form-data" action="process_reimbursement.php" method="post">
          <table cellpadding="3" cellspacing="0" border="0" width="100%">
            <tbody>
            	
                <td><label>Month To Apply To</label></td>
                <td >
                <select name="month" size="1">
        		<!-- BEGIN: monthoption -->
                    <option value="{month}" {monthselected}>{monthname}</option>
                <!-- END: monthoption -->    
                  </select>&nbsp;
                  <select name="year" size="1">
                    <!-- BEGIN: year -->
                    <option value="{year}" {yearselected} >{year}</option>
                    <!-- END: year -->
                  </select>
                </td>
              </tr>
              <tr>
                <td><label>Description</label></td>
                <td >
                	<input name="description" type="text" size="40" maxlength="255" />
                </td>
              </tr>
              <tr>
                <td><label>Value</label></td>
                <td >
                	<input name="val" type="text" size="10" maxlength="10" onblur="MM_setTextOfTextfield('val','',CurrencyFormatted(document.reimbursementform.val.value))" value="0.00"/>
                     <input name="submit" type="submit" value="Report" />
                    <input name="reset" type="reset" value="Reset" />
                </td>
              </tr>
              <tr>
                <td colspan="2"><div align="center"><br />
                    
                  </div></td>
              </tr>
            </tbody>
          </table>
        </form>
      </div>
      </fieldset>
      <!-- END: reimbursement -->
  </div>

</body>
</html>
<!-- END: main -->
