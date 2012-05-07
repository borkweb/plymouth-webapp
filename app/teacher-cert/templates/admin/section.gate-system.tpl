{box size=16}
	<form id="save-changes" action="{$PHP.BASE_URL}/admin/checklist-item/reorder">
		<button type="submit" class="btn disabled right" disabled="disabled">Save Changes</button>
	</form>
{/box}

{col size=10}
	{foreach from=$object->gates() item=gate}
		{capture assign=title}
			{$object->name} &raquo; {$gate->name}
		{/capture}
		{box title=$title class="gate-admin"}
			<div class="item-wrapper">
			<ul id="gate-{$gate->id}" class="gate" data-gate-id="{$gate->id}">
			{foreach from=$gate->checklist_items() item=checklist_item}
				<li class="item" data-item-id="{$checklist_item->id}">
					<strong>{$checklist_item->name}</strong>
					<span class="options">
						[ <a href="">Edit</a> ]
					</span>

					{if $checklist_item->is_date_field()}
						<span class="answer ckl-default">
							date
						</span>
					{elseif $checklist_item->is_text_field()}
						<span class="answer ckl-default">
							text
						</span>
					{else}
						<select name="checklist_item[{$checklist_item->id}]" class="answer">
							{foreach from=$checklist_item->answers() item=answer}
								<option value="{$answer->id}" {if $answer == $checklist_item->default_answer()}selected{/if}>{$answer->answer}</option>
							{/foreach}
						</select>
					{/if}
				</li>
			{/foreach}
			</ul>
			</div>
		{/box}
	{/foreach}
{/col}
{col size=6}
	{box title="Add Checklist Item"}
	{/box}
	{box title="Add Gate"}
	{/box}
{/col}

{literal}
<script>

$(function() {
	$('#save-changes').queue_form({
	});

	var changes = {};

	$('.gate-admin .gate').sortable({
		connectWith: '.gate',
		opacity: 0.9,
		start: function( event, ui ) {
			if( ui.helper !== undefined ) {
				ui.helper.css('position', 'absolute').css('margin-top', $(window).scrollTop() );
			}//end if
		},
		beforeStop: function( event, ui ) {
			if( ui.helper !== undefined ) {
				ui.helper.css('margin-top', 0 );
			}//end if
		},
		receive: function( event, ui ) {
			var $el = $( ui.item );
			var $parent = $(this);

			$('#save-changes').data('queue_form').queue('items', $el.data('item-id'), $parent.data('gate-id'));

			var url = BASE_URL + '/admin/checklist-item/' + $el.data('item-id') + '/move/' + $parent.data('gate-id');

			$('#save-changes :disabled').removeAttr('disabled').removeClass('disabled').closest('form').find('button[type=submit]').addClass('primary').html('Save Changes');
		}
	});

	$.waypoints.settings.scrollThrottle = 30;
	$('#save-changes').closest('.box').waypoint(function(e, direction) {
		$(this).toggleClass('sticky', direction === "down");
		e.stopPropagation();
	});
});
</script>
{/literal}
