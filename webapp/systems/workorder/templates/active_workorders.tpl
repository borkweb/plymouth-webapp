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
        	<td><a href="active_workorders.html?o=id">WO#</a></td>
            <td><a href="active_workorders.html?o=name">Name</a></td>
            <!-- <td>Device</td> -->
            <td>Manufacturer</td>
            <td>Model</td>
            <td>Serial</td>
            <td align="center"><a href="active_workorders.html?o=university_owned">PSU</a></td>
            <td><a href="active_workorders.html?o=time_entered">Date Entered</a></td>
            <td><a href="active_workorders.html?o=current_status">Current Status</a></td>
            <td><a href="active_workorders.html?o=tech_assigned">Tech Assigned</a></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <!-- BEGIN: workorderrow -->
        <tr {rowclass}>
        	<td><a href="detail_history.html?id={wo}" target="_top">{wo}</a></td>
            <td><strong><a href="history_list.html?u={username}" target="_top" >{name}</a></strong></td>
            <!-- <td>{type}</td> -->
            <td>{manufacturer}</td>
            <td>{model}</td>
            <td><strong>{serial}</strong></td>
            <td align="center">{psu_owned}</td>
            <td>{date}</td>
            <td {status_class}>{status}</td>
            <td>{tech}</td>
            <td><a href="admin_display.html?id={wo}&t=p" target="_top">print</a></td>
            <td><a href="admin_display.html?id={wo}&t=e" target="_top">edit</a></td>
        </tr>
        <!-- END: workorderrow -->
    </tbody>
    </table>
</body>
</html>
<!-- END: main -->
