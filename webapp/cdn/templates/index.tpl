{box size=8 title="Target Directory"}
	<form id="view-path" action="{$PHP.BASE_URL}/path" method="get">
		<ul>
			<li>
				<label>Directory:</label>
				<input type="text" size="40" name="p">
				<input type="submit" value="Go">
			</li>
		</ul>
	</form>
{/box}

{box size=8 title="Common Directories"}
	<ul>
		<li><a href="{$PHP.BASE_URL}/path/webapp/ape">APE</a></li>
		<li><a href="{$PHP.BASE_URL}/path/webapp/style">Webapp Style</a></li>
		<li><a href="{$PHP.BASE_URL}/path/webapp/graduate">CoGS Application</a></li>
	</ul>
{/box}
