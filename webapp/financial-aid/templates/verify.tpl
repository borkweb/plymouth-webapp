{box size=16 title="Verify Identity"}
	<p>Student financial data is protected by state and federal laws. In order to view this information,
	you must first verify your identity. This information will be checked against the Free Application
	for Federal Student Aid (FAFSA) on file at Plymouth State University. You will only have to provide
	this information once for each financial aid year you wish to view.</p>

	<form action="{$PHP.BASE_URL}/_verify" method="post">
	<ul class="label-left">
		<li>
			<label>Last 4 of Parent SSN:</label>
			<input type="password" name="last4" size="6" maxlength="4">
		</li>
		<li>
			<label>Birth Date:</label>
			{html_select_date time='2011--' start_year=1900 reverse_years=true}
		</li>
		<li>
			<label>&nbsp;</label>
			<input type="submit" value="Verify Me">
		</li>
	</ul>
	</form>
{/box}
