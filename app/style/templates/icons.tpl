{box title="Icons" style=hero size=16 id=icons}{/box}
{box title="Icon Sizes" size=16}
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Size</th>
				<th>Icon</th>
				<th>Icon w/ <code>boxed=true</code></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><code>size=tiny</code></td>
				<td>{icon id="ape-lock" size=tiny}</td>
				<td>{icon id="ape-lock" size=tiny boxed=true}</td>
			</tr>
			<tr>
				<td><code>size=small</code></td>
				<td>{icon id="ape-lock" size=small}</td>
				<td>{icon id="ape-lock" size=small boxed=true}</td>
			</tr>
			<tr>
				<td><code>size=medium</code></td>
				<td>{icon id="ape-lock" size=medium}</td>
				<td>{icon id="ape-lock" size=medium boxed=true}</td>
			</tr>
			<tr>
				<td><code>size=large</code></td>
				<td>{icon id="ape-lock" size=large}</td>
				<td>{icon id="ape-lock" size=large boxed=true}</td>
			</tr>
			<tr>
				<td><code>size=beefy</code></td>
				<td>{icon id="ape-lock" size=beefy}</td>
				<td>{icon id="ape-lock" size=beefy boxed=true}</td>
			</tr>
			<tr>
				<td><code>size=massive</code></td>
				<td>{icon id="ape-lock" size=massive}</td>
				<td>{icon id="ape-lock" size=massive boxed=true}</td>
			</tr>
			<tr>
				<td><code>size=ridiculous</code></td>
				<td>{icon id="ape-lock" size=ridiculous}</td>
				<td>{icon id="ape-lock" size=ridiculous boxed=true}</td>
			</tr>
		</tbody>
	</table>
{/box}
{box title="Icon Options"}
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>Option</th>
				<th>Code</th>
				<th>Result</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Sub Icon</td>
				<td><pre class="prettyprint">&#123;icon id="map" sub="ape-lock" size=beefy boxed=true&#125;</pre></td>
				<td>{icon id="map" sub="events" size=beefy boxed=true}</td>
			</tr>
			<tr>
				<td>Sub Value</td>
				<td><pre class="prettyprint">&#123;icon id="map" value="20" size=beefy boxed=true&#125;</pre></td>
				<td>{icon id="map" value="20" size=beefy boxed=true}</td>
			</tr>
			<tr>
				<td>Sub Value w/ <code>type=important</code></td>
				<td><pre class="prettyprint">&#123;icon id="map" value="20" type=important size=beefy boxed=true&#125;</pre></td>
				<td>{icon id="map" value="20" type=important size=beefy boxed=true}</td>
			</tr>
			<tr>
				<td>Sub Value w/ <code>type=info</code></td>
				<td><pre class="prettyprint">&#123;icon id="map" value="20" type=info size=beefy boxed=true&#125;</pre></td>
				<td>{icon id="map" value="20" type=info size=beefy boxed=true}</td>
			</tr>
			<tr>
				<td>Inherit styles. <code>flat=true</code></td>
				<td><pre class="prettyprint">&lt;span style="color:red;"&gt;&#123;icon id="map" flat=true size=beefy&#125;&lt;/span&gt;</pre></td>
				<td><span style="color:red;">{icon id="map" flat=true size=beefy}</span></td>
			</tr>
			<tr>
				<td>Add a class</td>
				<td>
					<pre class="prettyprint">
&lt;style&gt;
  .bork { 
    color: #bada55;
  }
&lt;/style&gt;
&#123;icon id="map" flat=true size=beefy class="bork"&#125;
					</pre>
				</td>
				<td>
					<style>.bork{ color:#bada55; }</style>
					{icon id="map" size=beefy class="bork"}
				</td>
			</tr>
		</tbody>
	</table>
{/box}

{box title="Icons"}
	<table class="table table-bordered table-striped">
		<thead>
			<tr>
				<th>ID</th>
				<th>Icon</th>
				<th>Boxed Icon</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$icons item=icon}
			<tr>
				<td><code>id={$icon}</code></td>
				<td>{icon id=$icon size=large}</td>
				<td>{icon id=$icon size=large boxed=true}</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
{/box}
