<!-- BEGIN: main -->
<table width="100%" align="center" valign="top" cellpadding="5" cellspacing="1">
<tr>
	<th>Name</th>
	<th>Phone</th>
	<th>Options</th>
</tr>
<!-- BEGIN: tlc_user_list -->
<tr>
	<td><font color="red">{row.last_name}, {row.first_name}</font> (<a href="tlc_user_profile.html?user_name={row.user_name}" title="View {row.first_name} {row.last_name}'s Profile In A New Window." onclick="window.open(this.href, this.target,'toolbar=yes,location=yes,directories=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,copyhistory=yes,width=400,height=300,screenX=150,screenY=300,top=150,left=300'); return false;">{row.user_name}</a>)</td>
	<td align="center">{row.phone}</td>
	<td align="center"><a href="tlc_user_profile.html?user_name={row.user_name}" title="View {row.first_name} {row.last_name}'s Profile." class="action"> &#187;&#187; View Profile &#187;&#187; </a></td>
</tr>
<!-- END: tlc_user_list -->
</table>
<!-- END: main -->