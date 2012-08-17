{box size="16" title="Schedule"}
	<table class="grid" width="100%">
		<thead>
			<tr>
				<th>Task</th>
				<th>File</th>
				<th>Frequency</th>
				<th>Time</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Retrieve TMS Payment Plan Feed Files</td>
				<td>get_files.php</td>
				<td class="centered">Daily</td>
				<td class="centered">8:00 pm</td>
			</tr>
			<tr>
				<td>Parse TMS Payment Plan Feed Files</td>
				<td>parse_files.php</td>
				<td class="centered">Daily</td>
				<td class="centered">8:15 pm</td>
			</tr>
			<tr>
				<td>Process Payment Plan Contracts</td>
				<td>payment_plan_contract.php</td>
				<td class="centered">Daily</td>
				<td class="centered">9:15 pm</td>
			</tr>
			<tr>
				<td>Process Payment Plan Disbursements</td>
				<td>payment_plan_disbursement.php</td>
				<td class="centered">Daily</td>
				<td class="centered">8:45 pm</td>
			</tr>
			<tr>
				<td>Refresh Balance Reports</td>
				<td>balance_report.php</td>
				<td class="centered">Daily</td>
				<td class="centered">11:00 pm</td>
			</tr>
			<tr>
				<td>Refresh Prebilling Application</td>
				<td>prebilling.php</td>
				<td class="centered">Daily</td>
				<td class="centered">10:15 pm</td>
			</tr>
			<tr>
				<td>Feed/Finance Pull</td>
				<td>get_from_unh.php</td>
				<td class="centered">M-F</td>
				<td class="centered">11:00 pm</td>
			</tr>
			<tr>
				<td>Feed/Finance Push</td>
				<td>feed_to_unh.php</td>
				<td class="centered">M-F</td>
				<td class="centered">6:30 pm</td>
			</tr>
			<tr>
				<td>E-Commerce Feed File Arrival</td>
				<td>N/A</td>
				<td class="centered">Daily</td>
				<td class="centered">4:00 pm</td>
			</tr>
			<tr>
				<td>E-Commerce Feed File Loading</td>
				<td>load.php</td>
				<td class="centered">Every 15 Minutes</td>
				<td class="centered">N/A</td>
			</tr>
			<tr>
				<td>E-Commerce Feed Processing</td>
				<td>ecommerce_process.php</td>
				<td class="centered">M-F</td>
				<td class="centered">4:20 pm</td>
			</tr>
		</tbody>
	</table>
{/box}
