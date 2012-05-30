<div class="paging">
{if $pages.pagination.last_page > 1}
<div class="pager">
Pages: 
{if $pages.pagination.current_page ne 1}
	{if $pages.pagination.current_page ne 2}
		<a href="1">&lt;&lt;</a>
	{/if}
	<a href="{$pages.pagination.previous_page}">&lt;-</a>
{/if}
{section loop=$pages.pagination.last_page+1 start=1 name=looper}
	{assign var=looper value=$smarty.section.looper.index}
	{if $looper < $pages.pagination.current_page+6 && $looper > $pages.pagination.current_page-6}
		{if $looper eq $pages.pagination.current_page}
			<a href="{$looper}" class="selected">{$looper}</a>
		{else}
			<a href="{$looper}">{$looper}</a>
		{/if}
	{/if}
{/section}
{if $pages.pagination.current_page ne $pages.pagination.last_page}
	<a href="{$pages.pagination.next_page}">-&gt;</a>
	{if $pages.pagination.current_page ne $pages.pagination.last_page}
		<a href="{$pages.pagination.last_page}">&gt;&gt;</a>
	{/if}
{/if}
</div>
{/if}
<div class="tracker">
Channels: {$pages.pagination.display_start}-{$pages.pagination.display_end} out of {$pages.pagination.total_records}
</div>
<div class="clear"></div>
</div>
