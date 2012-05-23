<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Order</title>
<link href="templates/css/style.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript">

</script>
</head>
<body>
<div align="center">

    	
        <table  id="ratetable" cellpadding="10" cellspacing="0">
        <tr>
        	<td colspan="2"><div class="headertext" align="center">PSU Computer Store Rate Schedule</div></td>
        </tr>
        <tr >
        	<td width="65%"><strong>Service</strong></td>
            <td><strong>Rate</strong></td>
        </tr>
        <!-- BEGIN: raterow -->
        <tr {rowclass}>
        	<td>{item}</td>
            <td>{rate}</td>
        </tr>
        <!-- END: raterow -->
        </table>
</div>
</body>
</html>
<!-- END: main -->
