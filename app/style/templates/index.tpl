{col size="4"}
	{box title='Header Examples' subheader="Example HTML Elements"}
		<h1>Header 1</h1>
		<h2>Header 2</h2>
		<h3>Header 3</h3>
		<h4>Header 4</h4>
		<h5>Header 5</h5>
		<h6>Header 6</h6>
		<img src="/images/1x1trns.gif" class="icon-small icon-small-smile"/>
		<img src="/images/1x1trns.gif" class="icon-medium icon-medium-smile"/>
		<img src="/images/1x1trns.gif" class="icon-big icon-big-smile"/>
	{/box}
	{box title='Basic Styling Examples' subheader="Example HTML Elements"}
		<a href="#">This is a link</a><br/><br/>
		<strong>&lt;strong&gt; Text</strong><br/><br/>
		<em>&lt;em&gt; Text</em><br/><br/>
		The following is an &lt;hr&gt; tag:
		<hr/>
		<blockquote>
			This text is in a &lt;blockquote&gt;
		</blockquote>
		<br/><br/>
		This is an <abbr title="Abbreviation">abbr</abbr> tag.<br/><br/>
		This is an <acronym title="Acronym">acronym</acronym> tag.<br/><br/>
	{/box}
{/col}

{col size="6"}
	{box title='Paragraph Example' subheader="Example HTML Elements"}
		All this text is not in a paragraph. All this text is not in a paragraph.All this text is not in a paragraph.All this text is not in a paragraph.All this text is not in a paragraph.
		<p>
			This text is <strong>in</strong> a &lt;p&gt; tag.
		</p>
		All this text is not in a paragraph.All this text is not in a paragraph.All this text is not in a paragraph.All this text is not in a paragraph.All this text is not in a paragraph.
	{/box}

	{box title='Person Data' subheader="Lazy-loaded person data"}
		<ul class="psu-lazyload" data-object=person data-id=200443>
			<li class="lazy-field" data-type="login_name"></li>
			<li class="lazy-field" data-type="email"></li>
			<li class="lazy-field" data-type="id"></li>
			<li class="lazy-field" data-type="wp_id"></li>
			<li class="lazy-field" data-type="first_name"></li>
			<li class="lazy-field" data-type="last_name"></li>
		</ul>
	{/box}

	{box title='List Example' subheader="Example HTML Elements"}
		Bulletted List
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
		Numbered List
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
	Bulletted List with class="clean"
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
	<p>List of type <tt>ul.compact</tt>:</p>
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
{/box}
{/col}

{col size="6"}
	{box title='Tabulated Data' subheader="Example HTML Elements"}
		<table>
			<tr>
				<th>Col 1</th>
				<th>Col 2</th>
				<th>Col 3</th>
			</tr>
			<tr>
				<td>A</td>
				<td>B</td>
				<td>C</td>
			</tr>
			<tr>
				<td>1</td>
				<td>2</td>
				<td>3</td>
			</tr>
			<tr>
				<td>bork</td>
				<td>moo</td>
				<td>whee</td>
			</tr>
			<tr>
				<td>rofl</td>
				<td>lol</td>
				<td>roflmao</td>
			</tr>
		</table>

		<p>Use <code>&lt;table class="sortable"></code> to sort table client-side
		in JavaScript. You <em>must</em> use <code>&lt;thead></code> and <code>&lt;tbody></code>.</p>

		<table class="sortable">
			<thead>
				<tr>
					<th>Col 1</th>
					<th>Col 2</th>
					<th>Col 3</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>A</td>
					<td>B</td>
					<td>C</td>
				</tr>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
				<tr>
					<td>bork</td>
					<td>moo</td>
					<td>whee</td>
				</tr>
				<tr>
					<td>rofl</td>
					<td>lol</td>
					<td>roflmao</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th>footer label</th>
					<td>is</td>
					<td>in da footer</td>
				</tr>
			</tfoot>
		</table>

		<p>Here, <tt>table.grid</tt>, with some highlighted rows. Uses <tt>.focus</tt>. Try clicking things.</p>

		<script type="text/javascript">
		$('#highlight-test tbody td').live('click', function(){
			var $o = $(this);

			if( $o.hasClass('focus') ) {
				$o.removeClass('focus').closest('tr').removeClass('highlight');
			} else {
				$o.closest('tbody').find('tr.highlight').removeClass('highlight').end()
					.find('td.focus').removeClass('focus').end().end()
					.closest('tr').addClass('highlight').end()
					.addClass('focus');
			}

		});
		</script>
		<table class="grid" id="highlight-test">
			<thead>
				<tr>
					<th>Col 1</th>
					<th>Col 2</th>
					<th>Col 3</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>A</td>
					<td>B</td>
					<td>C</td>
				</tr>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
				</tr>
				<tr class="highlight">
					<td>bark</td>
					<td>moo</td>
					<td>meow</td>
				</tr>
				<tr class="highlight">
					<td>red</td>
					<td class="focus">green</td>
					<td>blue</td>
				</tr>
				<tr>
					<td>Paris</td>
					<td>New York</td>
					<td>Boston</td>
				</tr>
				<tr>
					<td>Bears</td>
					<td>Beets</td>
					<td>Battlestar Galactica</td>
				</tr>
			</tbody>
		</table>
	{/box}

	{box title='Obscure Styling Examples' subheader="Example HTML Elements"}
		<address>
			Address Tag<br/>
			17 High St<br/>
			Plymouth, NH 03264
		</address>
		<bdo dir="rtl">This is a <abbr title="bdo">bdo</abbr> tag. I didn't know about this one so I really just put it in here to remember that it exists :)</bdo>
		<br/><br/>
		The following is a Definition List a.k.a. &lt;dl&gt; tag.
		<dl>
			<dt>This is the &lt;dt&gt; tag</dt>
			<dd>This is the &lt;dd&gt; tag</dd>
		</dl>
		<br/><br/>
		The following is an element with class="obscure"<br/>
		<span class="obscure">ohhai2u</span>
	{/box}


{/col}
<br class="clear"/>

{col size="16"}
	{box title='Form Data' subheader="Example HTML Elements"}
		<form>
			<ul class="bulleted">
				<li>Lists inside forms can force bullets using <tt>.bulleted</tt>.</li>
			</ul>
		</form>
		<div class="clear"></div>
		<div class="grid_5 alpha omega">
		<form>
			<fieldset>
				<legend>Fieldset Legend</legend>
				<ul>
					<li>
						<label class="required">Required Label:<em>*</em></label>
						<input type="text" value="Text Field"/>
					</li>
					<li>
						<label class="required">
							<span class="inherit">Unstyled</span> Span:<em>*</em>
							<span>Use span.inherit for JavaScript targeting.</span>
						</label>
						<input type="text" value="Text Field"/>
					</li>
					<li>
						<label>
							Label:
							<span>This is some helper text</span>
						</label>
						<input type="password" value="Password"/>
					</li>
					<li>
						<label>Label:</label>
						<select>
							<option>Option 1</option>
							<option>Option 2</option>
							<option>Option 3</option>
						</select>
					</li>
					<li>
						<label>This is a long Label:</label>
						<textarea>
							This is a textarea
						</textarea>
					</li>
					<li>
						<label>Label:</label>
						<input type="checkbox"/><br/>
						<input type="radio"/>
					</li>
					<li>
						<input type="button" value="input tag"/>
						<button>button tag</button>
						<a href="#" class="button">&lt;a> button</a>
					</li>
				</ul>
			</fieldset>
		</form>
		</div>
		<div class="grid_11 alpha omega">
		<form class="label-left">
			<fieldset>
				<legend>Form with class="label-left"</legend>
				<ul>
					<li>
						<label class="required">Required Label:<em>*</em></label>
						<input type="text" value="Text Field"/>
					</li>
					<li>
						<label class="required">
							<span class="inherit">Unstyled</span> Span:<em>*</em>
							<span>Use span.inherit for JavaScript targeting.</span>
						</label>
						<input type="text" value="Text Field"/>
					</li>
					<li>
						<label>Uneditable:</label>
						<span class="uneditable-input">Some value</span>
					</li>
					<li>
						<label>
							Label:
							<span>This is some helper text</span>
						</label>
						<input type="password" value="Password"/>
					</li>
					<li>
						<label>Input Prepend:</label>
						<div class="input-prepend">
							<span class="add-on">@</span>
							<input type="email"/>
						</div>
					</li>
					<li>
						<label>Input Append:</label>
						<div class="input-append">
							<input type="email"/>
							<span class="add-on active">@</span>
						</div>
					</li>
					<li>
						<label>Label:</label>
						<select>
							<option>Option 1</option>
							<option>Option 2</option>
							<option>Option 3</option>
						</select>
					</li>
					<li>
						<label>This is a really really really long Label:</label>
						<textarea>This is a textarea</textarea>
					</li>
					<li>
						<label>Label:</label>
						<input type="checkbox"/><br/>
						<input type="radio"/>
					</li>
					<li class="well">
						<div>
							<input type="button" value="input tag"/>
							<button>button tag</button>
							<a href="#" class="button">&lt;a> button</a>
						</div>
						<div style="margin-top: 0.5em;">
							<button class="btn primary">btn primary</button>
							<button class="btn info">btn info</button>
							<button class="btn success">btn success</button>
							<button class="btn danger">btn danger</button>
						</div>
					</li>
				</ul>
			</fieldset>
		</form>
		</div>
		<br class="clear"/>
	{/box}

	{box title='Box 2' title_size='8' title_right='09/29/2009' subheader="banana pancakes"}
		This is a full width column
	{/box}

	{box title='Box 3' style="clear"}
		This is a box with the style = "<strong>clear</strong>".
	{/box}

	{box}
		This is a box with no title bar.
	{/box}
{/col}
<br class="clear"/>
<div class="message-container">
<div class="message message-messages">
This is an <a href="#">example message</a>.
</div>
</div>

<div class="message-container">
<div class="message message-warnings">
This is an <a href="#">example warning message</a>.
</div>
</div>

<div class="message-container">
<div class="message message-errors">
This is an <a href="#">example error message</a>.
</div>
</div>

<div class="message-container">
<div class="message message-successes">
This is an <a href="#">example success message</a>.
</div>
</div>

<div class="message-container">
<div class="message message-messages">
<ul>
<li>This is the first message in a multi-message block.</li>
<li>This is the second message in a multi-message block.</li>
</ul>
</div>
</div>

{col size="4"}
	{box title='Squirrel Handed' title_size='3' secondary_title='LOL' subheader="banana pancakes"}
		some people are that
	{/box}
{/col}

{col size="6"}
	{box title='Squirrel Handed' subheader="banana pancakes"}
		some people are that
	{/box}

	{box title='Squirrel Handed' title_size="3" secondary_title="Moo" subheader="banana pancakes" style="clear"}
		some people are that
	{/box}

	{box title='Squirrel Handed' subheader="banana pancakes"}
		some people are that
	{/box}
{/col}

{col size="6"}
	{box title='Squirrel Handed' subheader="banana pancakes"}
		some people are that
	{/box}
{/col}
