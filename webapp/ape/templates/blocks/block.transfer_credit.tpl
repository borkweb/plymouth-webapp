{foreach from=$person->student->all_levels item=level}
	{assign var=student value=`$person->student->$level`}
	{foreach name=transfer_credit from=$student->transfer_credit item=item}
		{if $smarty.foreach.transfer_credit.first}
		<div id="ape_transfer_credit" class="ape-section {if $myuser->go_states.ape_transfer_credit === '0'}ape-section-hidden{/if}">
			<h3>Student's {$level|@substr:0:2|@strtoupper} Transfer Credit Applied</h3>	
			{assign var=transfer_credit_out value=true}
			<table class="grid">
				<thead>
				<tr>
					<th>Term/Semester</th>
					<th>Transfer Course ID</th>
					<th>Transfer Course Title</th>
					<th>Transfer Course Credits</th>
					<th>PSU Equivalent</th>
					<th>PSU Credit Hours</th>
					<th>General Ed.</th>
					<th>College/University</th>
				</tr>
				</thead>
				<tbody>
		{/if}
		<tr>
			<td>{$item.term_code}</td>
			<td>{$item.tc_id}</td>
			<td>{$item.tc_title}</td>
			<td>{$item.tc_credits}</td>
			<td>{$item.equivalent}</td>
			<td>{$item.credit_hours}</td>
			<td>{$item.general_ed}</td>
			<td>{$item.college}</td>
		</tr>
		{if $smarty.foreach.transfer_credit.last}
		</tbody></table></div>
		{/if}
	{/foreach}
{/foreach}
