<!-- BEGIN: main -->
<!-- BEGIN: quotas -->
<!-- BEGIN: PrintQuota -->
<ul class="user-info print-funds">
	<li class="print-balance">
		<label>Print Balance:</label> $<span class="balance">{print_balance}</span> <!-- BEGIN: print_balance_adjust --> - <a href="#">Adjust Balance</a><!-- END: print_balance_adjust -->
	</li>
	<li class="add-funds-throbber">
		Adjusting funds...<img src="/images/icons/16x16/animations/throbber.gif" style="text-align: middle;"/>
	</li>
	<li class="error add-funds-no_record">
		This user has not yet used any print balance.  You may not increase or decrease an unused balance.
	</li>
	<li class="error add-funds-invalid_privs">
		You do not have the appropriate privileges to adjust print balances.
	</li>
	<li class="error add-funds-too_small">
		You may not decrease the print balance below $0.00.  Why are you so evil?
	</li>
	<li class="success add-funds-success">
		Print Balance successfully updated.
	</li>
	<li class="add-funds">
		<button class="add" type="button">Adjust</button> print balance by: <select name="fund_increase">
			<optgroup label="Increase">
				<option value="1">+ $1</option>
				<option value="5">+ $5</option>
				<option value="10">+ $10</option>
				<option value="20">+ $20</option>
				<option value="0.1">+ $0.10</option>
			</optgroup>
			<optgroup label="Decrease">
				<option value="-1">- $1</option>
				<option value="-5">- $5</option>
				<option value="-10">- $10</option>
				<option value="-20">- $20</option>
				<option value="-0.1">- $0.10</option>
			</optgroup>
		</select>.
		<div style="clear: both;"><small><em>(All adjustments are logged)</em></small></div>
	</li>
</ul>
<!-- END: PrintQuota -->
<table width="100%" align="center" valign="middle" cellpadding="7" cellspacing="1">
	<!-- BEGIN: diskQuota -->
	<tr>
		<td>
			<label>My Drive:</label>
		</td>
		<td>
			<table width="100%">
				<tr>
					<td>
						<table style="height:10px; width:99%;" align="center">
							<tr>
								<td style="width:{DiskQuotaWidthUsage}%; background-color:{disk_bg_color};" class="quotatext" align="center"></td>
								<td style="width:{DiskQuotaWidthTotal}%; background-color:#FFFFFF;" class="quotatext">{UsersQuota}</td>
							</tr>
						</table>
					</td>
					<td width="45" class="quotatext">{MaxQuota}</td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- END: diskQuota -->
	<!-- BEGIN: departmentQuota -->
	<tr>
		<td>
			<label>Dept:</label>
		</td>
		<td>
			<table width="100%">
				<tr>
					<td>
						<table style="height:10px; width:99%;" align="center">
							<tr>
								<td style="width: {DeptQuotaWidthUsage}%; background-color:{dept_bg_color};" class="quotatext" align="center"></td>
								<td style="width: {DeptQuotaWidthTotal}%; background-color:#FFFFFF;" class="quotatext">{DeptCurrentQuota}</td>
							</tr>
						</table>
					</td>
					<td width="45" class="quotatext">{DeptTotalQuota}</td>
				</tr>
			</table>
		</td>
	</tr>
	<!-- END: DepartmentalQuota -->
</table>
<!-- END: quotas -->
<!-- END: main -->
