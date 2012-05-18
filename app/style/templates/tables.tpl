{col size="16"}
{box title="Table Options"}
<table class="table table-bordered table-striped">
  <thead>
      <tr>
        <th>Name</th>
        <th>Class</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Default</td>
        <td class="muted">None</td>
        <td>No styles, just columns and rows</td>
      </tr>
      <tr>
        <td>Basic</td>
        <td>
          <code>.table</code>
        </td>
        <td>Only horizontal lines between rows</td>
      </tr>
      <tr>
        <td>Bordered</td>
        <td>
          <code>.table-bordered</code>
        </td>
        <td>Rounds corners and adds outer border</td>
      </tr>
      <tr>
        <td>Zebra-stripe</td>
        <td>
          <code>.table-striped</code>
        </td>
        <td>Adds light gray background color to odd rows (1, 3, 5, etc)</td>
      </tr>
      <tr>
        <td>Condensed</td>
        <td>
          <code>.table-condensed</code>
        </td>
        <td>Cuts vertical padding in half, from 8px to 4px, within all <code>td</code> and <code>th</code> elements</td>
      </tr>
			<tr>
        <td>Highlighted Row</td>
        <td>
          <code>.highlight</code> (on a <code>&lt;tr&gt;</code>)
					<br>or<br>
          <code>.focus</code> (on a <code>&lt;tr&gt;</code>)
        </td>
        <td>Highlights a row</td>
			</tr>
    </tbody>
  </table>

	<p>To see <code>.focus</code> in action, try clicking on table rows in the examples.</p>

	<script type="text/javascript">
	$('#table-examples tbody td').live('click', function(){
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
{/box}
{box title="Example tables" id="table-examples"}
{capture name="table_contents"}
		<thead>
			<tr>
				<th>#</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Username</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>1</td>
				<td>Adam</td>
				<td>Backstrom</td>
				<td>ambackstrom</td>
			</tr>
			<tr>
				<td>2</td>
				<td>Matthew</td>
				<td>Batchelder</td>
				<td>mtbatchelder</td>
			</tr>
			<tr>
				<td>3</td>
				<td>Daniel</td>
				<td>Bramer</td>
				<td>djbramer</td>
			</tr>
			<tr class="highlight">
				<td>4</td>
				<td>Nathan</td>
				<td>Porter</td>
				<td>nrporter</td>
			</tr>
			<tr class="highlight">
				<td>5</td>
				<td>Zachary</td>
				<td>Tirrell</td>
				<td>zbtirrell</td>
			</tr>
		</tbody>
{/capture}
<div class="grid_6 alpha">
	<h3>1. Default table styles</h3>
	<p>
		Tables are not styled by default!
	</p>
	<pre class="prettyprint">
&lt;table&gt;
 ...
&lt;/table&gt;
	</pre>
</div>
<div class="grid_9">
	<table>
	{$smarty.capture.table_contents}
	</table>
</div>
<div class="clear"></div>
<hr>
<div class="grid_6 alpha">
	<h3>2. Simple table</h3>
	<p>
		Adding the <code>.table</code> class to a table gives some bare-bones styling
	</p>
	<pre class="prettyprint">
&lt;table class="table"&gt;
 ...
&lt;/table&gt;
	</pre>
</div>
<div class="grid_9">
	<table class="table">
	{$smarty.capture.table_contents}
	</table>
</div>
<div class="clear"></div>
<hr>
<div class="grid_6 alpha">
	<h3>3. Zebra-striped table</h3>
	<p>
		Adding the <code>.table .table-striped</code> classes to a table gives zebra striping.
	</p>
	<pre class="prettyprint">
&lt;table class="table table-striped"&gt;
 ...
&lt;/table&gt;
	</pre>
	<p class="muted">
		<strong>Note:</strong> Striped tables use the <code>:nth-child</code> CSS selector and is not available in IE7-IE8.
	</p>
</div>
<div class="grid_9">
	<table class="table table-striped">
	{$smarty.capture.table_contents}
	</table>
</div>
<div class="clear"></div>
<hr>
<div class="grid_6 alpha">
	<h3>4. Bordered table</h3>
	<p>
		Adding the <code>.table .table-bordered</code> classes to a table gives borders.
	</p>
	<pre class="prettyprint">
&lt;table class="table table-bordered"&gt;
 ...
&lt;/table&gt;
	</pre>
</div>
<div class="grid_9">
	<table class="table table-bordered">
	{$smarty.capture.table_contents}
	</table>
</div>
<div class="clear"></div>
<hr>
<div class="grid_6 alpha">
	<h3>5. Condensed table</h3>
	<p>
		Adding the <code>.table .table-condensed</code> classes to a table lessens the cell padding.
	</p>
	<pre class="prettyprint">
&lt;table class="table table-condensed"&gt;
 ...
&lt;/table&gt;
	</pre>
</div>
<div class="grid_9">
	<table class="table table-condensed">
	{$smarty.capture.table_contents}
	</table>
</div>
<div class="clear"></div>
<hr>
<div class="grid_6 alpha">
	<h3>6. ALL THE THINGS!</h3>
	<p>
		You can combine all the classes!
	</p>
	<pre class="prettyprint">
&lt;table class="table table-bordered table-striped table-condensed"&gt;
 ...
&lt;/table&gt;
	</pre>
</div>
<div class="grid_9">
	<table class="table table-bordered table-striped table-condensed">
	{$smarty.capture.table_contents}
	</table>
</div>
{/box}
{/col}
