{box title="Opt-Out of Event Themes"}
<form method="post">
	<p class="description">
		Is an event theme annoying you?  Do you want your theme selection back?
		If so, feel free to opt out of the themes you do not wish to see.
	</p>

	<button type="submit">Save Settings</button>
	<!--<div>
		Check: <a href="#checkall" class="check checkall">All</a>, <a href="#checknone" class="check checknone">None</a>
	</div>-->
	<ul>
		{foreach from=$themes item=theme}
			{if $theme.level == 'event' || $theme.level == 'hidden-event'}
			<li>
				<input type="checkbox" value="{$theme.id}" name="theme[]" {if $optout[$theme.id]}checked="checked"{/if}/> {$theme.name}
			</li>
			{/if}
		{/foreach}
	</ul>
	<button type="submit">Save Settings</button>
</form>
{/box}
