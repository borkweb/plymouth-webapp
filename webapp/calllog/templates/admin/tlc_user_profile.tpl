<!-- BEGIN: main -->
<h2>&#187; TLC User Profile</h2>
<span class="page_content_summary">Showing {full_name}'s TLC Profile.</span>
<table align="center" valign="top" cellpadding="1" cellspacing="1" class="content">
<tr>
	<!-- BEGIN: user_profile -->
	<td width="380" align="left" class="content">
		<table align="center" valign="middle" cellpadding="1" cellspacing="1" class="content">
		<tr>
			<td align="center" valign="middle" class="content_head" nowrap="nowrap">
				<img src="{photo}" border="0" alt="No Photo Provided." />
			</td>
		</tr>
		</table>
	</td>
	<td width="60%" align="left" class="content">
		<table width="100%" align="center" valign="top" cellpadding="3" cellspacing="1" class="content">
		<!-- BEGIN: row_data -->
		<tr>
			<td width="100" align="left" valign="middle" class="content_head" nowrap="nowrap">
				{row_name}
			</td>
			<td align="left" valign="middle" class="content">
				{row_data}
			</td>
		</tr>
		<!-- END: row_data -->
		<!-- BEGIN: ldap_data -->
		<tr>
			<td width="100" align="left" valign="middle" class="content_head" nowrap="nowrap">
				{row_name}
			</td>
			<td align="left" valign="middle" class="content">
				{row_data}
			</td>
		</tr>
		<!-- END: ldap_data -->
		<!-- BEGIN: no_ldap_data -->
		<tr>
			<td width="100" align="left" valign="middle" class="content_head" nowrap="nowrap">
				{row_name}
			</td>
			<td align="left" valign="middle" class="content">
				{row_data}
			</td>
		</tr>
		<!-- END: no_ldap_data -->
		<!-- BEGIN: db_data -->
		<tr>
			<td width="100" align="left" valign="middle" class="content_head" nowrap="nowrap">
				{row_name}
			</td>
			<td align="left" valign="middle" class="content">
				{row_data}
			</td>
		</tr>
		<!-- END: db_data -->
		<!-- BEGIN: no_db_data -->
		<tr>
			<td width="100" align="left" valign="middle" class="content_head" nowrap="nowrap">
				{row_name}
			</td>
			<td align="left" valign="middle" class="content">
				{row_data}
			</td>
		</tr>
		<!-- END: no_db_data -->
		<!-- BEGIN: comments -->
		<tr>
			<td align="left" valign="top" class="content_head" colspan="2">
				Comments
			</td>
		</tr>
		<tr>
			<td align="left" valign="top" class="content" colspan="2">
				{comments}
			</td>
		</tr>
		<!-- END: comments -->
		</table>
	</td>
	<!-- END: user_profile -->
</tr>
<tr>
	<!-- BEGIN: no_profile -->
	<td width="100%" align="left" class="content" colspan="2">
		<table width="100%" align="center" valign="middle" cellpadding="1" cellspacing="1" class="content">
		<tr>
			<td width="500" align="left" valign="middle" class="content">
				<h2>One of the following three cases have caused this page to display</h2>
				
				
				<h4>Case #1 The Lightweight Directory Access Protocol (LDAP) server is down</h4>
				<p>The student and/or the faculty LDAP server may be down, if either is true, user profiles will not be available until LDAP is up and running again.</p>
				
				<h4>Case #2 Student/Faculty Data Inconsistency</h4>
				<p>If this user is a <strong>staff/faculty member</strong>, check out this user's information under the "user_privileges" column in the call_log_employee table.  The field should say "staff" in it, if it doesn't, change it by managing TLC users through CallLog, or by making the change directly to the database.  If this user is a <strong>student</strong>, check out the "user_privileges" field for this user in the call_log_employee table, and make sure the field is not null, or blank.  If the entry is not blank, there is a problem elsewhere.
				</p>
				
				<h4>Case #3 No User Profile</h4>
				<p>There is no profile for this user, or this user's information is inconsistent in the database.  This can occur with users such as <i>helpdesk</i>.</p>

			</td>
		</tr>
		</table>
	</td>
	<!-- END: no_profile -->
</tr>
</table>
<!-- END: main -->
