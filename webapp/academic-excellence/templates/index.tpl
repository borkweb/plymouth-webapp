{if $user_type eq 'admin'}
	{include file="index_admin.tpl"}
{elseif $user_type eq 'aestudent'}
	{if $PHP.ACCEPTING_DATA == false}
		{box}
		<p>The Academic Excellence process has been closed for this semester. If you have
		any questions, please contact Gale Beckwith at <a href="mailto:gbeckwith@plymouth.edu">gbeckwith@plymouth.edu</a>.</p>
		{/box}
	{else}
		{include file="index_student.tpl"}
	{/if}
{else}
	{box}
	<p>We are unable to process your request at the present time. Please contact Gale Beckwith
	at <a href="mailto:gbeckwith@plymouth.edu">gbeckwith@plymouth.edu</a> for more information.</p>
	{/box}
{/if}
