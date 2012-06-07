<ul class="grid_16">
	<li><a href="{$PHP.BASE_URL}/">Theme Selection</a></li>
	<li><a href="{$PHP.BASE_URL}/optout.html">Opt-Out of Event Themes</a></li>
	<li>
		<a href="#">Other Portal Preferences</a>
		<ul>
			<li><a href="{$PHP.BASE_URL}/fluid.html" class="fluid">{if $PHP.fluid}Disable Full Width{else}Enable Full Width{/if}</a></li>
			<li><a href="{$PHP.BASE_URL}/toggle_chat.html" class="disable_chat">{if $PHP.disabled_chat}Enable Chat Bar{else}Disable Chat Bar{/if}</a></li>
		</ul>
	</li>
</ul>
