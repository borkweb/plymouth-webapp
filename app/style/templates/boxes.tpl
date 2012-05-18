{box title="Boxes" style=hero size=16 id=boxes}{/box}
{box title="Boxes" size=16}
	<p>
		Boxes contain site content and can be used for sidebars, primary content areas, etc.  They can stretch
		the full width of the site or be as small as a single grid width!  Using <code>size=16</code> in a
		<code>&#123;box&#125;</code> declaration sets the width!
	</p>
	<p>
		Optionally, you can wrap boxes in <code>&#123;col&#125;</code> blocks to organize your page into columns
		of set widths.
		<pre class="prettyprint">
&#123;col size=4&#125;
  &#123;box title="Column 1 Box 1&#125;
    ...
  &#123;/box&#125;
  &#123;box title="Column 1 Box 2&#125;
    ...
  &#123;/box&#125;
&#123;/col&#125;
&#123;col size=12&#125;
  &#123;box title="Column 2 Box 1&#125;
	...
  &#123;/box&#125;
&#123;/col&#125;
		</pre>
	</p>

	<h3>Styles</h3>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Style</th>
				<th>What it looks like</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					Default (border box) <code>style=border</code>
				</td>
				<td>
					{box title="Default box"}
						<p>
							Some people are squirrel handed.
						</p>
						<pre class="prettyprint">
&#123;box title="Default box"&#125;
...
&#123;/box&#125;
						</pre>
					{/box}
				</td>
			</tr>
			<tr>
				<td>
					<code>style=clear</code>
				</td>
				<td>
					{box title="Clear box" style=clear}
						<p>
							These boxes don't have backgrounds or borders (save for between the title and body.
						</p>
						<pre class="prettyprint">
&#123;box title="Clear box" style=clear&#125;
...
&#123;/box&#125;
						</pre>
					{/box}
				</td>
			</tr>
			<tr>
				<td>
					<code>style=hero</code>
				</td>
				<td>
					{box title="Hero box" style=hero}
						<p>
							Big honkin' box. These boxes do not support secondary titles or subheaders.
						</p>
						<pre class="prettyprint">
&#123;box title="Hero box" style=hero&#125;
...
&#123;/box&#125;
						</pre>
					{/box}
				</td>
			</tr>
			<tr>
				<td>
					<code>style=no-pad</code>
				</td>
				<td>
					{box title="No-padding box" style="no-pad"}
						<p>
							These boxes don't have any padding for their content areas.
						</p>
						<pre class="prettyprint">
&#123;box title="No-padding box" style="no-pad"&#125;
...
&#123;/box&#125;
						</pre>
					{/box}
				</td>
			</tr>
			<tr>
				<td>
					Box with no <code>title</code>
				</td>
				<td>
					{box}
						<p>
							This box doesn't have a title set.
						</p>
						<pre class="prettyprint">
&#123;box size=16&#125;
...
&#123;/box&#125;
						</pre>
					{/box}
				</td>
			</tr>
		</tbody>
	</table>
{/box}

{box title="Box with secondary title and subheader" secondary_title="Secondary title" subheader="This is a subheader" title_size="8" size="16"}
	<p>
		Add a secondary title &amp; sub header like so:
	</p>
	<pre class="prettyprint">
&#123;box title="The title" title_size=8 secondary_title="Secondary title" subheader="omg hai" size=16&#125;
...
&#123;/box&#125;
	</pre>
{/box}
