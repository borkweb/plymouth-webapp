{if !$is_csv}
	<div class="block">
		<form name="date_select" method=post action="">
			<center>
				Display Transactions between {html_select_date prefix='StartDate' start_year='-5' end_year='+1' reverse_years=true time=$stime} and {html_select_date prefix='EndDate' start_year='-5' end_year='+1' reverse_years=true time=$etime} 
				<input type="submit" name="select_range" value="Go" />
				<input type="submit" name="csv_range" value="csv" />
			</center>
			
		</form>
	</div> 
	
	<div class="block">
		<table class="space">
			<tr>
				<th> Date/Time</th>
				<th>Transaction ID</th>
				<th>PSU ID Number</th>
				<th>Full Name</th>
				<th>Transaction Amount (amount deposited)</th>
			</tr>
				{if $data}
					{foreach from=$data item=trans}
						<tr>
							<td>{$trans.timestamp}</td>
							<td>{$trans.transactionid}</td>
							<td>{$trans.payerid}</td>
							<td>{$trans.payerfullname}</td>
							<td>{'%n'|money_format:$trans.totalamount/100}</td>
						</tr>
					{/foreach}
				{/if}
		</table>
	</div>
{else}{foreach from=$is_csv item=trans key=i}{if $i>0}{$newline}{/if}{$trans.timestamp},{$trans.transactionid},"{$trans.payerid}","{$trans.payerfullname}",{'%n'|money_format:$trans.totalamount/100}{/foreach}{/if}