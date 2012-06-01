<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Orders</title>
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body class="altbackground">
<div align="center">
<div id="main">

<div id="newlink" >
      <a href="javascript:history.back();" >Back</a>
    </div>
    <div id="logoutlink">
      <a href="{logouturl}" >Logout</a>
    </div>
    <br class="clear" />
<div id="historylist">
	<table width="100%" cellpadding="5" cellspacing="0" class="border">
    <tbody>
    	<tr class="tablehead">
        	<td align="left">WO#</td>
            <td align="left">Name</td>
            <!-- <td align="center">Device</td> -->
            <td align="left">Manufacturer</td>
            <td align="left">Model</td>
            <td align="center">PSU Property</td>
            <td align="center">Date Entered</td>
            <td align="center">Date Closed</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <!-- BEGIN: nodata -->
        <tr>
        	<td colspan="11">No Records Matching Your Criteria Were Found</td>
        </tr>
        <!-- END: nodata -->
        <!-- BEGIN: workorderrow -->
        <tr {rowclass}>
        	<td align="left"><a href="detail_history.html?id={wo}">{wo}</a></td>
            <td align="left"><a href="history_list.html?u={username}" >{name}</a></td>
            <!-- <td align="center">{type}</td> -->
            <td align="left">{manufacturer}</td>
            <td align="left">{model}</td>
            <td align="center">{psu_owned}</td>
            <td align="center">{opened}</td>
            <td align="center">{closed}</td>
            <td><a href="admin_display.html?id={wo}&t=p" target="_top">print</a></td>
            <td><a href="admin_display.html?id={wo}&t=p&i=1" target="_top">invoice</a></td>
            <td><!-- BEGIN: reopen --><a href="admin_display.html?id={wo}&t=e" target="_top">reopen</a><!-- END: reopen -->
            <!-- BEGIN: edit --><a href="admin_display.html?id={wo}&t=e" target="_top">edit</a><!-- END: edit --></td>
        </tr>
        <!-- END: workorderrow -->
    </tbody>
    </table>
</div>
</div>
</div>
</body>
</html>
<!-- END: main -->
