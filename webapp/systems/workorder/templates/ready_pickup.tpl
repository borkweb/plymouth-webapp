<!-- BEGIN: main -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PSU Computer Store Work Orders</title>
<link href="templates/css/backend.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
	<table width="100%" cellpadding="5" cellspacing="0">
    <tbody>
    	<tr class="tablehead">
        	<td>WO#</a></td>
            <td>Name</td>
            <td>Manufacturer</td>
            <td>Model</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <!-- BEGIN: pickuprow -->
        <tr {rowclass}>
        	<td ><a href="detail_history.html?id={wo}" target="_top"  >{wo}</a></td>
            <td><a href="history_list.html?u={username}" target="_top" >{name}</a></td>
            <td class="{idclass}">{manufacturer}</td>
            <td class="{idclass}">{model}</td>
            <td><a href="admin_display.html?id={wo}&t=p&i=1" target="_top">invoice</a></td>
            <td><a href="admin_display.html?id={wo}&t=e" target="_top">edit</a></td>
        </tr>
        <!-- END: workorderrow -->
    </tbody>
    </table>
</body>
</html>
<!-- END: main -->
