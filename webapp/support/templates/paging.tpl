<div class="paging">
{if $pages.pagination.last_page > 1}
<div class="pager">
Pages: 
{if $pages.pagination.current_page ne 1}
	{if $pages.pagination.current_page ne 2}
		<a href="{$pager}?{$page_name}=1">&lt;&lt;</a>
	{/if}
	<a href="{$pager}?{$page_name}={$pages.pagination.previous_page}">&lt;-</a>
{/if}
{section loop=$pages.pagination.last_page start=1 name=looper}
	{assign var=looper value=$smarty.section.looper.index}
	{if $looper < $pages.pagination.current_page+6 && $looper > $pages.pagination.current_page-6}
		{if $looper eq $pages.pagination.current_page}
			<a href="{$pager}?{$page_name}={$looper}" class="selected">{$looper}</a>
		{else}
			<a href="{$pager}?{$page_name}={$looper}">{$looper}</a>
		{/if}
	{/if}
{/section}
{if $pages.pagination.current_page ne $pages.pagination.last_page}
	<a href="{$pager}?{$page_name}={$pages.pagination.next_page}">-&gt;</a>
	{if $pages.pagination.current_page ne $pages.pagination.last_page-1}
		<a href="{$pager}?{$page_name}={$pages.pagination.last_page}">&gt;&gt;</a>
	{/if}
{/if}
</div>
{/if}
<div class="tracker">
Tickets: {$pages.pagination.display_start}-{$pages.pagination.display_end} out of {$pages.pagination.total_records}
</div>
<div class="clear"></div>
</div>
