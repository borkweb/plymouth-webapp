<ul id="raintix-results" class="grid_10 prefix_3 suffix_3 alpha omega">
	{foreach from=$people item=person}
		<li>
			<div class="head">
			<div class="major">{$person.major}</div>
			<h3>{$person.name_full}</h3>
			</div>
			{if $person.location == 'Foley Gymnasium'}
				{capture name="loc_url"}pecenter{/capture}
				{capture name="mms_url"}pec{/capture}
			{elseif $person.location == 'HUB Courtroom'}
				{capture name="loc_url"}hub{/capture}
				{capture name="mms_url"}hub{/capture}
			{else}
				{capture name="loc_url"}silvercenter{/capture}
				{capture name="mms_url"}silver{/capture}
			{/if}
			<div class="location">
				<label>Inclement Weather Location:</label> 
				<a href="http://www.plymouth.edu/commencement/undergraduate/weather.html#{$smarty.capture.loc_url}">{$person.location}</a>
				<br/>
				<a href="mms://sirius.plymouth.edu/{$smarty.capture.mms_url}" style="vertical-align:middle;"><img src="/images/icons/22x22/devices/video-display.png"/></a> Watch the <a href="mms://sirius.plymouth.edu/{$smarty.capture.mms_url}">Streaming Video</a> live!
			</div>
		</li>
	{foreachelse}
		<li>Your search returned no results.  Please adjust your search and try again or contact the Registrar's Office at 603-535-2345</li>
	{/foreach}
</ul>
