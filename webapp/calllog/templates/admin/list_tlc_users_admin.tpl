<!-- BEGIN: main -->
<table width="100%" cellpadding="3" cellspacing="1">
<tr>
	<th>Name</th>
	<th>User name</th>
	<th>Work Phone</th>
	<th>Cell Phone</th>
	<th>Home Phone</th>
</tr>
<!-- BEGIN: tlc_user_list -->
<tr>
	<td><a href="tlc_users_admin.html?action=edittlcuser&user_name={row.user_name}">{row.last_name}, {row.first_name}</a></td>
	<td>{row.user_name}</td>
	<td>{row.work_phone}</td>
	<td>{row.cell_phone}</td>
	<td>{row.home_phone}</td>
</tr>
<!-- END: tlc_user_list -->
</table>
<!-- END: main -->