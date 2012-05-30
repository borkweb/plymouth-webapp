<div class="outer"><div class="middle"><div class="inner">
	<div id="listing">
		{if $response.exit_code > 0}
			<p class="error"><span class="badge"></span>Error: {$response.error} ({$response.exit_code})</p>
		{else}
			{strip}
			<table>
			<thead>
				<tr>
					<th class="name"><a href="?sort=name{if $sort eq 'name'}&order={if $order eq 'asc'}desc{else}asc{/if}{/if}">Filename</a> (<a href="#TB_inline?height=200&amp;width=400&amp;inlineId=filter" class="thickbox">filter</a>{if $filter}, <a href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:browse{$dirpath}">clear filter</a>{/if})</th>
					<th class="actions">Actions</th>
					<th class="mtime"><a href="?sort=mtime{if $sort eq 'mtime'}&order={if $order eq 'asc'}desc{else}asc{/if}{else}&order=desc{/if}">Modified</a></th>
					<th class="size"><a href="?sort=size{if $sort eq 'size'}&order={if $order eq 'asc'}desc{else}asc{/if}{/if}">Size</a></th>
				</tr>
			</thead>
			<tbody>
			{if $parent}
				<tr class="dir">
					<td class="name" colspan="4"><a href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:browse{$parent}"><span class="badge"></span>Parent Directory</a></td>
				</tr>
			{/if}
			{foreach from=$listing item=file}
				<tr class="{$file.type}" id="rf_row{counter}">
					<td class="name">
						{if $file.type eq 'file'}
							<a href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:view{$dirpath}{$file.name|escape:'url'}"><span class="badge"></span></a>
						{else}
							<a href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:browse{$dirpath}{$file.name|escape:'url'}/"><span class="badge"></span></a>
						{/if}
						{if $file.write}
							<span class="rename">{$file.name|escape}</span>
						{else}
							{$file.name|escape}
						{/if}
					</td>
					<td class="actions">
					{if $file.type eq 'file'}
						{if $file.read}
						<a class="badge view" href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:view{$dirpath}{$file.name|escape:'url'}"
							title="View &ldquo;{$file.name|escape:'html'}&rdquo;"><span>(view)</span></a>
						<a class="badge download" href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:download{$dirpath}{$file.name|escape:'url'}"
							title="Download &ldquo;{$file.name|escape:'html'}&rdquo;"><span class="badge"></span><span>(download)</span></a>
						{/if}
						{if $file.write}
						<a class="badge delete" href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:unlink{$dirpath}{$file.name|escape:'url'}?redirect=1" onclick="rf_unlink(this); return false;"
							title="Delete &ldquo;{$file.name|escape:'html'}&rdquo;"><span>(delete)</span></a>
						<a class="badge chmod" href="#" onclick="rfjs.chmod(this); return false;"
							title="Make &ldquo;{$file.name|escape:'html'}&rdquo; world writable"><span>(chmod)</span></a>
						{*<a class="badge copy" href="#" onclick="rfjs.copy(this); return false;"
							title="Make a copy of &ldquo;{$file.name|escape:'html'}&rdquo;"><span>(copy)</span></a>*}
						{/if}
					{/if}
					</td>
					<td class="mtime">{$file.mtime|date_format:'%Y-%m-%d %H:%M'}</td>
					<td class="size">{if isset($file.size)}<span class="size">{$file.size|default:'0'} {$file.size_unit}</span>{/if}</td>
				</tr>
			{foreachelse}
				<tr>
					<td class="name">
						<p class="error"><span class="badge"></span>This directory is currently empty{if $filter}, or your filter does not match any files{/if}.</span>
					</td>
					<td class="actions"></td>
					<td class="mtime"></td>
					<td class="size"></td>
				</tr>
			{/foreach}
			</tbody>
			</table>
			{/strip}
		{/if}
	</div>
</div></div></div>

{if $response.exit_code eq 0}
	{if $can_write}
	<div class="outer"><div class="middle"><div class="inner">
		<h2>Upload File</h2>
		<div id="upload">
			<div id="upload-html">
				<form action="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:upload{$dirpath}" method="post" enctype="multipart/form-data" onsubmit="rf_upload();">
				<input type="hidden" name="MAX_FILE_SIZE" value="15728640">
				<input type="hidden" name="fullpath" value="{$path}">
				<span class="badge"></span>
				<input type="file" name="rf_file" size="30"> <input type="submit" value="Upload">
				<span id="status">Uploading...</span>
				<br class="clear">
				</form>
			</div>
			<div id="upload-swf">
				<span class="badge"></span><span id="upload-placeholder"></span><span id="upload-placeholder-content">Select Files to Upload</span>. (<a href="#" onclick="jQuery('#upload-html, #upload-swf').toggle(); return false;">Use basic uploader</a>?)
				<ul id="swflog"></ul>
				<a id="refresh-page" href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:browse{$path}">Refresh Listing &rarr;</a>
			</div>
			<p>Maximum upload size: 15MB.</p>
		</div>
	</div></div></div>
	{/if}

	<div id="filter" class="hidden">
		<form action="{$PHP.BASE_URL}/filter.php" method="get">
			<div>
				<span>{$dirpath}</span><input type="text" id="filter-input" name="filter" value="enter filter here" size="20">
			</div>
			<input type="submit" class="submit" name="submit" value="filter">
			<input type="hidden" name="server" value="{$PHP.SSH_HOST|escape}">
			<input type="hidden" name="path" value="{$dirpath|escape}">
		</form>
		<br class="clear">
		<p>Asterisks (&ldquo;*&rdquo;) may be used as a wildcard. Examples:</p>
		<ul>
			<li><span>glrletr*</span> -- files starting with &ldquo;glrletr&rdquo;</li>
			<li><span>*.log</span> -- files ending with &ldquo;.log&rdquo;</li>
			<li><span>sfr*.lis</span> -- files starting with &ldquo;sfr&rdquo; and ending with &ldquo;.lis&rdquo;</li>
		</ul>
	</div>
{/if}

<p class="dlist"><a href="{$PHP.BASE_URL}/{$PHP.SSH_HOST}:">&larr; Directory List</a></p>

<script type="text/javascript">
var path = "{$path|escape:'javascript'}";
var dirpath = "{$dirpath|escape:'javascript'}";
var server = "{$PHP.SSH_HOST|escape:'javascript'}";
var files = [{foreach from=$listing item=file}'{$file.name|escape:'javascript'}',{/foreach}];
</script>
<script type="text/javascript" src="{$PHP.BASE_URL}/js/upload.js?v=1232983440"></script>
