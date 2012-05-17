{col size="16"}
	{box title='Headings &amp; body copy'}
		<div class="grid_8 alpha well">
			<h1>Header 1</h1>
			<h2>Header 2</h2>
			<h3>Header 3</h3>
			<h4>Header 4</h4>
			<h5>Header 5</h5>
			<h6>Header 6</h6>
		</div>
		<div class="grid_7 omega">
			<h3>Example body text</h3>
			<p>
				Nullam quis risus eget urna mollis ornare vel eu leo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam id dolor id nibh ultricies vehicula ut id elit.
			</p>
			<p>
				Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Donec sed odio dui.
			</p>
		</div>
	{/box}
	{box title='Basic Styling Examples'}
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>wat?</th>
					<th>How it looks</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>&lt;a&gt;</code></td>
					<td>
						<a href="#">This is a link</a><br/>
					</td>
				</tr>
				<tr>
					<td><code>&lt;strong&gt;</code></td>
					<td>
						<strong>I am strong</strong>
					</td>
				</tr>
				<tr>
					<td><code>&lt;em&gt;</code></td>
					<td>
						<em>I am emphasized</em>
					</td>
				</tr>
				<tr>
					<td><code>&lt;hr&gt;</code></td>
					<td>
						<hr>
					</td>
				</tr>
				<tr>
					<td><code>&lt;abbr&gt;</code></td>
					<td>
						This is an <abbr title="Abbreviation">abbr</abbr> tag.
					</td>
				</tr>
				<tr>
					<td><code>&lt;acronym&gt;</code></td>
					<td>
						This is an <acronym title="Acronym">acronym</acronym> tag.
					</td>
				</tr>
				<tr>
					<td><code>&lt;address&gt;</code></td>
					<td>
						<address>
							Address Tag<br/>
							17 High St<br/>
							Plymouth, NH 03264
						</address>
					</td>
				</tr>
				<tr>
					<td><code>&lt;bdo&gt;</code></td>
					<td>
						<bdo dir="rtl">This is a <abbr title="bdo">bdo</abbr> tag. I didn't know about this one so I really just put it in here to remember that it exists :)</bdo>
					</td>
				</tr>
				<tr>
					<td><code>&lt;dd&gt;&lt;dt/&gt;&lt;dd/&gt;</code></td>
					<td>
						<dl>
							<dt>This is the &lt;dt&gt; tag</dt>
							<dd>This is the &lt;dd&gt; tag</dd>
						</dl>
					</td>
				</tr>
				<tr>
					<td>Obscured via <code>.obscure</code> class</td>
					<td>
						<span class="obscure">ohhai2u</span>
					</td>
				</tr>
				<tr>
					<td>Muted via <code>.muted</code> class</td>
					<td>
						<span class="muted">I am muted text</span>
					</td>
				</tr>
			</tbody>
		</table>
	{/box}
	{box title="Blockquotes"}
		<table class="grid" width="100%">
			<thead>
				<tr>
					<th>Element</th>
					<th>Usage</th>
					<th>Optional</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>&lt;blockquote&gt;</code></td>
					<td>Block-level element for quoting content from another source</td>
					<td>
						<p>
							Add <code>cite</code> attribute for source URL
						</p>
						<p>
							Use <code>.pull-left</code> and <code>.pull-right</code> classes for floated options
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<h3>Example blockquotes</h3>
		<div class="grid_8 alpha">
			<p>Default blockquotes are styled as such:</p>
			<blockquote>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante venenatis.</p>
        <small>Someone famous in <cite title="">Body of work</cite></small>
      </blockquote>
		</div>
		<div class="grid_7 omega">
			<p>To float your blockquote to the right, add <code>class="pull-right"</code>:</p>
			<blockquote class="pull-right">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante venenatis.</p>
        <small>Someone famous in <cite title="">Body of work</cite></small>
      </blockquote>
		</div>
		<div class="clear"></div>
	{/box}
	{box title="Lists"}
		<div class="grid_4 alpha">
		<h3>Bulletted List</h3>
		<ul>
			<li>Item 1</li>
			<li>Item 2</li>
			<li>
				Item 3
				<ul>
					<li>Item 3.1</li>
					<li>Item 3.2</li>
					<li>Item 3.3</li>
				</ul>
			</li>
		</ul>
		</div>
		<div class="grid_4">
		<h3>Numbered List</h3>
		<ol>
			<li>Item 1</li>
			<li>Item 2</li>
			<li>
				Item 3
				<ol>
					<li>Item 3.1</li>
					<li>Item 3.2</li>
					<li>Item 3.3</li>
				</ol>
			</li>
		</ol>
		</div>
		<div class="grid_4">
		<h3>Clean List</h3>
		<p>Use <code>class="clean"</code></p>
		<ul class="clean">
			<li>Item 1</li>
			<li>Item 2</li>
			<li>
				Item 3
				<ul class="clean">
					<li>Item 3.1</li>
					<li>Item 3.2</li>
					<li>Item 3.3</li>
				</ul>
			</li>
		</ul>
		</div>
		<div class="grid_3 omega">
		<h3>Compact List</h3>
		<p>Use <code>class="compact"</code></p>
		<ul class="compact">
			<li>Item 1</li>
			<li>Item 2</li>
			<li>
				Item 3
				<ul>
					<li>Item 3.1</li>
					<li>Item 3.2</li>
					<li>Item 3.3</li>
				</ul>
			</li>
		</ul>
		</div>
	{/box}
{/col}

