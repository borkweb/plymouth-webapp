<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Orders</title>
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
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
        <a href="admin.html" >Back To Admin</a>
    </div>
    <div id="logoutlink">
      <a href="{logouturl}" >Logout</a>
    </div>
    <br class="clear" />
    <div id="leftcol">
    <fieldset>
    <legend>Payments by Check</legend>
      <div class="infobox">
      <!-- BEGIN: checks -->
        <table cellpadding="3" cellspacing="0" border="0" width="100%">
        	<tr class="tablehead">
            	<td >WO #</td>
                <td>Name</td>
                <td align="center">Payment</td>
            </tr>
            <!-- BEGIN: none -->
            <tr>
            	<td colspan="3">No payments were made by check<br /><br  /></td>
            </tr>
            <!-- END: none -->
            <!-- BEGIN: row -->
            <tr {rowclass} >
            	<td><a href="detail_history.html?id={wo}" target="_top">{wo}</a></td>
                <td>{name}</td>
                <td align="right">${amount}</td>
            </tr>
            <!-- END: row -->
            <tr>
                <td colspan="3" align="right" class="totalrow">{num_checks} Transactions Totaling ${check_total}</td>
            </tr>
           
        </table>
         <!-- END: checks -->
      </div>
      </fieldset>
        <br />
      <fieldset>
    <legend>Payments by FOAPAL</legend>
      <div class="infobox">
      <!-- BEGIN: foapal -->
        <table cellpadding="3" cellspacing="0" border="0" width="100%">
        	<tr class="tablehead">
            	<td >WO #</td>
                <td>Name</td>
                <td align="center">Payment</td>
            </tr>
            <!-- BEGIN: none -->
            <tr>
            	<td colspan="3">No payments were made by FOAPAL<br /><br  /></td>
            </tr>
            <!-- END: none -->
            <!-- BEGIN: row -->
            <tr {rowclass} >
            	<td><a href="detail_history.html?id={wo}" target="_top">{wo}</a></td>
                <td>{name}</td>
                <td align="right">${amount}</td>
            </tr>
            <!-- END: row -->
            <tr>
                <td colspan="3" align="right" class="totalrow">{num_foapal} Transactions Totaling ${foapal_total}</td>
            </tr>
           
        </table>
         <!-- END: foapal -->
      </div>
      </fieldset>
      <br />
      <fieldset>
    <legend>Reimbursement Checks</legend>
      <div class="infobox">
      <!-- BEGIN: reimbursement -->
        <table cellpadding="3" cellspacing="0" border="0" width="100%">
        	<tr class="tablehead">
                <td>Description</td>
                <td align="center">Amount</td>
            </tr>
            <!-- BEGIN: none -->
            <tr>
            	<td colspan="3">No reimbursements recorded<br /><br  /></td>
            </tr>
            <!-- END: none -->
            <!-- BEGIN: row -->
            <tr {rowclass} >
                <td>{description}</td>
                <td align="right">${amount}</td>
            </tr>
            <!-- END: row -->
            <tr>
                <td colspan="3" align="right" class="totalrow">{num_reimbursement} Reimbursements Totaling ${reimbursement_total}</td>
            </tr>
           
        </table>
         <!-- END: reimbursement -->
      </div>
      </fieldset>
    </div>
    
    <div id="rightcol">
    <fieldset>
     <legend>Payments by Credit Card</legend>
     <div class="infobox" >
         <!-- BEGIN: credit -->
        <table cellpadding="3" cellspacing="0" border="0" width="100%" >
        	<tr class="tablehead">
            	<td>WO #</td>
                <td>Name</td>
                <td>Payment</td>
            </tr>
            <!-- BEGIN: none -->
            <tr>
            	<td colspan="3">No payments were made by credit card <br  /><br  /></td>
            </tr>
            <!-- END: none -->
            <!-- BEGIN: row -->
            <tr {rowclass} >
            	<td><a href="detail_history.html?id={wo}" target="_top">{wo}</a></td>
                <td>{name}</td>
                <td align="right">${amount}</td>
            </tr>
            <!-- END: row -->
            <tr >
                <td align="right" colspan="3" class="totalrow">{num_credit} Transactions Totaling ${credit_total}</td>
            </tr>
           
        </table>
         <!-- END: credit -->
      </div>
      </fieldset>
      <br />
      <fieldset>
     <legend>Balance Sheet</legend>
     <div class="infobox" >
        <table cellpadding="3" cellspacing="0" border="0" width="100%" >
            <tr class="alternaterow" >
            	<th>Work Orders Closed</th>
                <td align="right">{wo_closed}</td>
            </tr>
            <tr class="row" >
            	<th>Number of PSU Warranty Repairs</th>
                <td align="right">{num_warranty_university}</td>
            </tr>
            <tr class="alternaterow" >
            	<th>Number of Student Warranty Repairs</th>
                <td align="right">{num_warranty_personal}</td>
            </tr>
            <tr class="row" >
            	<th>Number of Warranty Parts</th>
                <td align="right">{num_warranty}</td>
            </tr>
            <tr class="alternaterow" >
            	<th>Total Number of Ordered Parts</th>
                <td align="right">{num_parts}</td>
            </tr>
            <tr class="row">
            	<th>Cost of Parts</th>
                <td align="right">{parts_cost}</td>
            </tr>
            <tr class="alternaterow" >
            	<th>Parts Gross</th>
                <td align="right">{parts_charged}</td>
            </tr>
            <tr class="row">
            	<th>Proceed on Parts</th>
                <td align="right">{profit_parts}</td>
            </tr>           
            <tr class="alternaterow" >
            	<th>Hours of Labor</th>
                <td align="right">{labor_hours}</td>
            </tr>
            <tr class="row">
            	<th>Amount Charged for Labor</th>
                <td align="right">{labor_charged}</td>
            </tr>
            <tr class="alternaterow" >
            	<th>Reimbursement Checks</th>
                <td align="right">{reimbursement_total}</td>
            </tr>
            <tr class="row" >
            	<th>Gross</th>
                <td align="right">{gross}</td>
            </tr>
            <tr class="alternaterow">
            	<th>Net Proceeds</th>
                <td align="right">{net}</td>
            </tr>
        </table>
         <!-- END: credit -->
      </div>
      </fieldset>
    </div>
    </div>
</div>
</body>
</html>
<!-- END: main -->
