{box title="Manage versions in `$path`" size=16}
	<form id="manage-files" action="{$PHP.BASE_URL}/update" method="post">
	<input type="hidden" name="from" value="{$path|escape}">
	<table class="grid">
		<thead>
			<tr>
				<th>Path</th>
				<th>Version</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$files key=path item=file}
			<tr class="{' '|implode:$file->tags} selectable">
				<td>
					<input type="checkbox" name="cdnfiles[]" value="{$file->formkey|escape}">
					<a href="{$path|cdn}">{$path}</a>
				</td>
				<td>{$file->version}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>

	<p id="cdn-actions">
		Select: <a id="sel-all" href="#">All</a>, <a id="sel-none" href="#">None</a>, <a id="sel-db" href="#">In CDN</a>, <a id="sel-nodb" href="#">Not in CDN</a>, <a id="sel-nofs" href="#">404</a>.

		With selected:
		<select name="action">
			<option>Bump</option>
			<!--<option>Remove</option>-->
		</select>
		<input type="submit" value="Do">
	</p>
	</form>
{/box}
