{assign var=reserve value=$reservation[$reservation_idx]}
{if $reserve.delivery_type=='1'}
	{assign var="type" value="CTS Sponsored"}
{else}
	{assign var="type" value="Pickup"}
{/if}
{assign var="start_date" value=$reserve.start_date|date_format:$date_format}
{assign var="end_date" value=$reserve.end_date|date_format:$date_format}
{assign var="start_time" value=$reserve.start_time|date_format:$time_format}
{assign var="end_time" value=$reserve.end_time|date_format:$time_format}
{box size=16}
	<div class="grid_8 grid-internal">
	<span class="print">Reservation {$reservation_idx} for: {$reserve.fname} {$reserve.lname} ({$type})</span>
	<hr>
		
		<h3>Event Information</h3>
		<ul class="label-left">
				<li><label>Location: </label>{$locations[$reserve.building_idx]} <strong>in room</strong> {$reserve.room}</li>
				<li><label>Title: </label>{$reserve.title}</li>
				<li><label>Priority of Loan: </label>{if $reserve.priority == 0}Normal{else}<strong>High</strong>{/if} </li>
				<li><label>Comments: </label><p>{$reserve.memo}</p></li>
				<li><label>Requested Items: </label><p>{$reserve.request_items}</p></li>

		</ul>
		{if $reserve.delivery_type == 1}
			<h3>Technician Assigned</h3>
			<ul class="label-left">

			<li><label>Dropoff: </label>
			{if $reserve.delivery_user}
				{html_options name=assigned_tech_dropoff options=$cts_technicians selected=$reserve.delivery_user disabled=true}
			{else}
				<span>No User Assigned</span>
			{/if}
				</li>

			<li><label>Pickup: </label>
			{if $reserve.retrieval_user}
				{html_options name=assigned_tech_pickup options=$cts_technicians selected=$reserve.retrieval_user disabled=true}
			{else}
				<span>No User Assigned</span>
			{/if}</li>

			</ul>
		{/if}
		<h3>Messages</h3>
			<ul class="clean">

			{foreach from=$messages item=message key=id}
				<li><label>{$message.author} at {$message.time|date_format:$time_format} on {$message.date|date_format:$date_format}: </label><span class="cts-message">{$message.message}</span></li>
			{/foreach}
			</ul>

	</div>

	<div class="grid_8 grid-internal">
	<span class="print">{$start_date} at {$start_time} to {$end_date} at {$end_time}</span> 
	<hr>

		<h3>Contact Information</h3>
		<ul class="label-left">
			<li><label>Phone: </label>{$reserve.phone}</li>
			{if $reserve.secondary_phone}
				<li><label>Secondary Phone: </label>{$reserve.secondary_phone}</li>
			{/if}
			<li><label>Email: </label>{$reserve.email}</li>
		</ul>
		{if $equipment_info }
			<h3>Equipment Assigned</h3>
			<table class="grid" width="450">
				<thead>
					<tr>
						<th>GLPI ID</th>
						<th>Type</th>
						<th>Model</th>
					</tr>
				</thead>
				<tbody>
				<!--need to access the data that is saved in equipment info -->
				{foreach from=$equipment_info item=row key=id}
					{foreach from=$equipment_info.$id item=equipment key=glpi_id}
					<tr>
						<td>{$glpi_id|substr:-4}</td>
						<td>{$equipment.type}</td>
						<td>{$equipment.model}</td>
					</tr>
					{/foreach}
				{/foreach}
				</tbody>
			</table>
		{else}
			<span class="bold">There is no equipment assigned to this request.</span>
		{/if}
	<ul>
		
		{if $subitems}
			<h3>Subitems</h3>
			<table class="grid" width="300">
				<thead>
					<tr>
						<th>Subitem</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$subitems item=subitem key=id}
					<tr>
						<td>{$subitem.name}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		{else}
			<span class="bold"> There are no subitems assigned to this request.</span>
		{/if}
	
	</ul>
</div>
<hr>
<ul>
	<li><span>Pickup:</span><span class="signature">Helpdesk Signature</span><span class="signature">Loanee Signature</span></li>
	<li><span>*</span></li>
	<li><span >Dropoff:</span><span class="signature">Helpdesk Signature</span><span class="signature">Loanee Signature</span></li>
	<li><span> *</span></li>
</ul>
{/box}


