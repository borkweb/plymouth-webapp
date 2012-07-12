{box title="Upload Feed File"}
	<form enctype="multipart/form-data" method="post" action="{$PHP.BASE_URL}/actions/upload-feed.php">
		<input type="file" name="feed" />
		<input type="submit" value="Upload" />
	</form>
{/box}

{box title="Common App Records"}
	<table class="grid">
		<thead>
			<tr>
				<td colspan="7" style="text-align:center">
					<span style="float: left;">
						&larr;
						{if $current.first === null}first{else}<a href="?{paging_querystring current=$current_shown count=$current.limit offset=$current.first}">first</a>{/if},
						{if $current.prev === null}prev{else}<a href="?{paging_querystring current=$current_shown count=$current.limit offset=$current.prev}">prev</a>{/if}
					</span>
					<span style="float: right;">
						{if $current.next === null}next{else}<a href="?{paging_querystring current=$current_shown count=$current.limit offset=$current.next}">next</a>{/if},
						{if $current.last === null}last{else}<a href="?{paging_querystring current=$current_shown count=$current.limit offset=$current.last}">last</a>{/if}
						&rarr;
					</span>
					Results {$current.range_start+1} - {$current.range_end} of
					{if $current.count < $current.total}
						<a href="?{paging_querystring current=$current offset=0 count=$current.total}">{$current.total}</a>
					{else}
						{$current.total}
					{/if}
					<!--(<a href="?">clear filters</a>)-->
				</td>
			</tr>
			<tr>
				<th>ID</th>
				<th>CA ID</th>
				<th>Filename</th>
				<th>Term</th>
				<th>Feed Date</th>
				<th>Update Date</th>
				<th>Load Date</th>
			</tr>
		</thead>
		{foreach from=$records item=record}
			<tr>
				<td>{$record.id}</td>
				<td>{$record.common_app_client_id}</td>
				<td>{$record.file_name}</td>
				<td>{$record.term_id}</td>
				<td>{$record.feed_date}</td>
				<td>{$record.update_date}</td>
				<td>{$record.load_date}</td>
			</tr>
		{/foreach}
	</table>
{/box}
