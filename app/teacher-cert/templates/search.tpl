{box size=5 title="Search Students"}
	<form class="qry-students">
		<ul>
			<li>
				<label>Gate system:</label>
				<select name="qry-students-gs">
					{foreach from=$gatesystems item=system}
						<option value="{$system->id}">{$system->name}</option>
					{/foreach}
				</select>
			</li>
			<li>
				<label>Filter:</label>
				<input type="search" class="qry-students-q wide" name="q" placeholder="Search by Name or ID&hellip;">
			</li>
		</ul>
	</form>
{/box}

{box size=11 title="Search Results" class="s-noquery qry-container"}
	<p class="qry-please">Please enter a search query.</p>
	<table class="qry-results grid wide">
		<thead>
			<tr>
				<th>Name</th>
				<th>ID</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
{/box}

<script type="text/javascript">
$(function(){
	teacher_cert.search.ready()
});
</script>
