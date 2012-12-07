{if !$is_csv}
	<div class="block">
		<form name="date_select" method="get" action="">
			<center>
				Display 
				<select name="processor">
					<option value="">All</option>
					{html_options options=$processors selected=$smarty.get.processor}
				</select>
				Transactions between 
				{html_select_date prefix='start_date_' start_year='-5' end_year='+1' reverse_years=true time=$start_date} 
				and 
				{html_select_date prefix='end_date_' start_year='-5' end_year='+1' reverse_years=true time=$end_date} 
				<input type="submit" name="select_range" value="Go" />
				<input type="submit" name="csv_range" value="csv" />
			</center>
			
		</form>
	</div> 
	<div class="block">
		
		<div class="info">
			<div class="head" style="text-align: center;">
				<h3>{$selected_processor}</h3>
				From {$friendly_start_date} To {$friendly_end_date}
			</div>
			
			<ul class="info">
				<li style="margin-left: 0;">
					<label>Rule Code:</label> CR05
				</li>
				<li>
					<label>Bank Code:</label> U3
				</li>
				{if $foapal.fund}<li><label>Fund:</label> {$foapal.fund}</li>{/if}
				{if $foapal.org}<li><label>Org:</label> {$foapal.org}</li>{/if}
				{if $foapal.acct}<li><label>Acct:</label> {$foapal.acct}</li>{/if}
				{if $foapal.prog}<li><label>Prog:</label> {$foapal.prog}</li>{/if}
				{if $foapal.actv}<li><label>Activity:</label> {$foapal.actv}</li>{/if}
				{if $foapal.locn}<li><label>Location:</label> {$foapal.locn}</li>{/if}
			</ul>
		</div>
	
		<table class="report space" style="font-size:0.8em;" width="100%">
			<tr>
				<th class="psu" style="text-align: left;width: 7em;">Date/Time</th>
				<th style="width: 5em;">Doc Number</th>
				<th style="width: 5em;">Payment Type</th>
				<th class="psu" style="width: 7em;">Transaction ID</th>
				<th class="psu" style="width: 7em;">Order Number</th>
				<th class="psu" style="text-align: left;">Order Name</th>
				<th style="width: 11em;">Line Description</th>
				<th class="money" style="width: 7em;">Debit Amount</th>
				<th class="money" style="width: 7em;">Credit Amount</th>
				<th style="width: 3em;">DC</th>
			</tr>
				{if $data}
					{assign var=grand_total value=`0`}
					{foreach from=$data item=doc key=fileid}
						{assign var=doc_total value=`0`}
						{foreach from=$doc.transactions item=trans}
							{assign var=foapal value=`$trans.user_choice1`}
							{assign var=doc_total value=`$doc_total+$trans.dollar_amount-$trans.debit_amount`}
							<tr>
								<td>{$trans.date}</td>
								<td style="text-align: center;">{$trans.fileid}</td>
								<td style="text-align: center;">{$trans.accounttype}</td>
								<td style="text-align: center;">{$trans.transactionid}</td>
								<td style="text-align: center;">{$trans.ordernumber}</td>
								<td>{$trans.ordername}</td>
								<td style="text-align: center;">{$trans.ordertype|default:'PSU Commerce Manager'}</td>
								<td class="money">${$trans.debit_amount|default:'0'|number_format:2}</td>
								<td class="money">${$trans.dollar_amount|default:'0'|number_format:2}</td>
								<td style="text-align: center;">{$trans.debit_credit|default:'+'}</td>
							</tr>
						{/foreach}
						<tr class="sub-total">
							<td colspan="6" class="label">
								Debit/Credit Totals <span>{$fileid}</span>:
							</td>
							<td class="value">
								 ${$doc.debit_total|default:'0'|number_format:2}
							</td>
							<td class="value">
								 ${$doc.credit_total|default:'0'|number_format:2}
							</td>
							<td></td>
						</tr>
						{assign var=grand_total value=`$grand_total+$doc_total`}
						<tr class="total">
							<td colspan="7" class="label">
								Total for Doc Number <span>{$fileid}</span>:
							</td>
							<td class="value">
								 ${$doc_total|default:'0'|number_format:2}
							</td>
							<td></td>
						</tr>
					{/foreach}
						<tr class="total">
							<td colspan="7" class="label">
								Grand Total:
							</td>
							<td class="value">
								 ${$grand_total|default:'0'|number_format:2}
							</td>
							<td></td>
						</tr>
				{/if}
		</table>
	</div>
{else}
{strip}
	"Timestamp","Doc Number","Account Type","Transaction ID","Order Number","Order Name","Line Description","Debit Amount","Credit Amount","DC","FOAPAL"
	{foreach from=$is_csv item=doc key=fileid}
		{assign var=doc_total value=`0`}
		{foreach from=$doc.transactions item=trans}
			{$newline}{$trans.timestamp},{$trans.fileid},{$trans.accounttype},{$trans.transactionid},{$trans.ordernumber},"{$trans.ordername}","{$trans.ordertype|default:'PSU Commerce Manager'}",{$trans.debit_amount|default:'0'|number_format:2},{$trans.dollar_amount|default:'0'|number_format:2},"{$trans.debit_credit|default:'+'}","{$trans.userchoice1}"
		{/foreach}
	{/foreach}
{/strip}
{/if}