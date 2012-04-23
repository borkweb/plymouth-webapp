<div id="ape_adv_letter_history" class="ape-section {if $myuser->go_states.ape_adv_letter_history === '0'}ape-section-hidden{/if}">
	<h3>Letter History</h3>
	<ul class="apedata">
		{foreach name=letter_history from=$person->alumni->letter_history item=item}
			{if $smarty.foreach.letter_history.first}
			<li>
				<table class="grid sortable">
					<thead>
					<tr>
						<th>Date</th>
						<th>Letter</th>
						<th>Originator</th>
					</tr>
					</thead>
					<tbody>
			{/if}
			<tr>
				<td>{$item.date_printed|date_format:"%b %e, %Y %I:%m %p"}</td>
				<td>{$item.letter}</td>
				<td>{$item.originator}</td>
			</tr>
			{if $smarty.foreach.letter_history.last}</tbody></table></li>{/if}
			
		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>
</div>
