{if $caller.username == $call.call_log_username}{assign var='user_submitted' value=true}{/if}
{capture name="type"}{if $is_caller}support{else}calllog{/if}{/capture}
({$call.call_status|capitalize}) {$call.title|default:"Support Ticket #`$call.call_id`"}{if $call.call_priority!='normal'} [{$call.call_priority|@strtoupper}]{/if}

Ticket #{$call.call_id}

{if !$is_caller}
Original Ticket Date: {$call.call_date|date_format:"%b %e, %Y %r"}
Caller: {$pcache[$caller_id]->formatName('f m l')} ({$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"})
{if $caller.dept}
Caller Department: {$caller.dept}{/if}
{if $caller.location}
Caller Location: {$caller.location}{/if}
{if $caller.phone_number}
Caller Phone Number: {$caller.phone_number}{/if}
{/if}

{if !$is_caller}

{if $current.logger}Updated by: {$pcache[$current.logger]->formatName('f m l')} ({$pcache[$current.logger]->username|default:"`$pcache[$current.logger]->wp_id`"}){/if}
{else}

{if $current.logger}Updated by: {$pcache[$current.logger]->formatName('f')}{/if}
{/if}

{if $current.update_date}Updated: {$current.update_date|date_format:"%b %e, %Y %r"}{/if}
{if !$is_caller}

{if $current.assigned_to}Assigned to: {$pcache[$current.assigned_to]->formatName('f m l')} ({$pcache[$current.assigned_to]->username|default:"`$pcache[$current.assigned_to]->wp_id`"}){/if}
{if $current.group_name}Group: {$current.group_name}{/if}
{/if}
{if $call.feelings}
Feelings: {$call.feelings}{/if}

======================================================
= Ticket Update
======================================================
{$current.comments|default:"No details entered"}

{if $call.call_status == 'closed'}
This ticket has been closed.  View the ticket here: https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}.
{else}
Respond/Reply to this Ticket here: https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}
{/if}
{if $history}

======================================================
= Ticket History
======================================================
{foreach from=$history item=item}
Updated by: {$pcache[$item.logger]->formatName('f m l')} ({$pcache[$item.logger]->username|default:"`$pcache[$item.logger]->wp_id`"})
Updated: {$item.update_date|date_format:"%b %e, %Y %r"}
{if $item.assigned_to}Assigned to: {$pcache[$item.assigned_to]->formatName('f m l')} ({$pcache[$item.assigned_to]->username|default:"`$pcache[$item.assigned_to]->wp_id`"}){/if}
{if $item.group_name}Group: {$item.group_name}{/if}


{$item.comments|default:"<em>No details entered</em>"|nl2br}
------------------------------------------------------

{/foreach}
{/if}
======================================================
= Additional Information
======================================================
You are receiving this email because you are attached to this Plymouth State University support ticket{if !$is_caller} or its assigned group{/if}.

If you wish to respond or add to this ticket, please do so here: https://www.plymouth.edu/webapp/{$smarty.capture.type}/ticket/{$call.call_id}.

{if !$is_caller}
This ticket is attached to {$pcache[$caller_id]->formatName('f m l')}: http://go.plymouth.edu/ape/{$pcache[$caller_id]->username|default:"`$pcache[$caller_id]->wp_id`"} (APE)
{/if}
