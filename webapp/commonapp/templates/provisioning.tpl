{box title="Applicants Without Email Addresses" subheader="A list of the users whose myPlymouth invitation could not be sent, due to a missing email address."}
	<p>There are currently {$missing|@count} active record(s) in this table.</p>

	<p class="cleanup">Persons marked in red are no longer applicants, and should be ignored.
	<a href="actions/cleanup-provisioning.php">Cleanup records for non-applicants</a>?</p>

	<table id="missing" class="grid">
		<tr>
			<th>&nbsp;</th>
			<th>Pidm</th>
			<th>Reason</th>
			<th>PSU ID</th>
			<th>LDAP User</th>
			<th>External User</th>
			<th>First Name</th>
			<!--
			<th>Email</th>
			<th>Term Code</th>
			-->
			<th>First Reported</th>
			<th>Last Reported</th>
		</tr>
		{foreach from=$missing item=user name=users}
			<tr id="pidm-{$user.pidm}" class="userrow missing">
				<td>{$smarty.foreach.users.iteration}.</td>
				<td><a href="http://go.plymouth.edu/ape/{$user.pidm}">{$user.pidm}</a></td>
				<td>{$user.reason}</td>
				<td class="psuid"></td>
				<td class="ldap_user"></td>
				<td class="username"></td>
				<td class="name_first"></td>
				<!--
				<td class="email"></td>
				<td class="term_code_entry"></td>
				-->
				<td>{$user.activity_date}</td>
				<td>{$user.updated_date}</td>
			</tr>
		{/foreach}
	</table>
{/box}
