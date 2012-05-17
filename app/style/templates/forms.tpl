{box title="Buttons!" size=16}
	<div class="well">
		<div>
			<input type="button" value="input tag"/>
			<button>button tag</button>
			<a href="#" class="btn">&lt;a> button</a>
		</div>
	</div>
	<table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Button</th>
        <th>class=""</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><button class="btn" href="#">Default</button></td>
        <td><code>btn</code></td>
        <td>Standard gray button with gradient</td>
      </tr>
      <tr>
        <td><button class="btn btn-primary" href="#">Primary</button></td>
        <td><code>btn btn-primary</code></td>
        <td>Provides extra visual weight and identifies the primary action in a set of buttons</td>
      </tr>
      <tr>
        <td><button class="btn btn-info" href="#">Info</button></td>
        <td><code>btn btn-info</code></td>
        <td>Used as an alternative to the default styles</td>
      </tr>
      <tr>
        <td><button class="btn btn-success" href="#">Success</button></td>
        <td><code>btn btn-success</code></td>
        <td>Indicates a successful or positive action</td>
      </tr>
      <tr>
        <td><button class="btn btn-warning" href="#">Warning</button></td>
        <td><code>btn btn-warning</code></td>
        <td>Indicates caution should be taken with this action</td>
      </tr>
      <tr>
        <td><button class="btn btn-danger" href="#">Danger</button></td>
        <td><code>btn btn-danger</code></td>
        <td>Indicates a dangerous or potentially negative action</td>
      </tr>
      <tr>
        <td><button class="btn btn-inverse" href="#">Inverse</button></td>
        <td><code>btn btn-inverse</code></td>
        <td>Alternate dark gray button, not tied to a semantic action or use</td>
      </tr>
    </tbody>
  </table>
{/box}
{box title="Four types of forms" size=16}
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
        <th>Vertical (default)</th>
        <td><code>.form-vertical</code> <span class="muted">(not required)</span></td>
        <td>Stacked, left-aligned labels over controls</td>
      </tr>
      <tr>
        <th>Inline</th>
        <td><code>.form-inline</code></td>
        <td>Left-aligned label and inline-block controls for compact style</td>
      </tr>
      <tr>
        <th>Search</th>
        <td><code>.form-search</code></td>
        <td>Extra-rounded text input for a typical search aesthetic</td>
      </tr>
      <tr>
        <th>Horizontal</th>
        <td><code>.form-horizontal</code></td>
        <td>Float left, right-aligned labels on same line as controls</td>
      </tr>
    </tbody>
  </table>
{/box}

{box title="Example forms" size=16}
	<h3>Basic form</h3>
	<p>
		Smart and lightweight defaults without extra markup.
	</p>
	<form class="well">
		<label>Label name</label>
		<input type="text" class="span3" placeholder="Type something...">
		<p class="help-block">Example block-level help text here.</p>
		<label class="checkbox" style="display:block;">
			<input type="checkbox"> Check me out
		</label>
		<button type="submit" class="btn">Submit</button>
	</form>

	<h3>Search form</h3>
	<p>Add <code>.form-search</code> to the form and <code>.search-query</code> to the input.</p>
	<form class="well form-search">
		<input type="text" class="input-medium search-query">
		<button type="submit" class="btn">Search</button>
	</form>

	<h3>Inline form</h3>
	<p>Add <code>.form-inline</code> to finesse the vertical alignment and spacing of form controls.</p>
	<form class="well form-inline">
		<input type="text" class="input-small" placeholder="Email">
		<input type="password" class="input-small" placeholder="Password">
		<label class="checkbox">
			<input type="checkbox"> Remember me
		</label>
		<button type="submit" class="btn">Sign in</button>
	</form>

	<h3>Horizontal forms</h3>
	<p>
	</p>
	<form class="form-horizontal">
		<fieldset>
			<div class="control-group">
				<label class="control-label" for="input01">Text input</label>
				<div class="controls">
					<input type="text" class="input-xlarge" id="input01">
					<p class="help-block">In addition to freeform text, any HTML5 text-based input appears like so.</p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="optionsCheckbox">Checkbox</label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" id="optionsCheckbox" value="option1">
						Option one is this and that
					</label>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="select01">Select list</label>
				<div class="controls">
					<select id="select01">
						<option>something</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="multiSelect">Multicon-select</label>
				<div class="controls">
					<select multiple="multiple" id="multiSelect">
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="fileInput">File input</label>
				<div class="controls">
					<input class="input-file" id="fileInput" type="file">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="textarea">Textarea</label>
				<div class="controls">
					<textarea class="input-xlarge" id="textarea" rows="3"></textarea>
				</div>
			</div>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary">Save changes</button>
				<button class="btn">Cancel</button>
			</div>
		</fieldset>
	</form>

	<h3>Fancy form stuff</h3>
	<form class="form-horizontal">
		<fieldset>
			<div class="control-group">
				<label class="control-label" for="focusedInput">Focused input</label>
				<div class="controls">
					<input class="input-xlarge focused" id="focusedInput" type="text" value="This is focused...">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Uneditable input</label>
				<div class="controls">
					<span class="input-xlarge uneditable-input">Some value here</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="disabledInput">Disabled input</label>
				<div class="controls">
					<input class="input-xlarge disabled" id="disabledInput" type="text" placeholder="Disabled input here..." disabled="">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="optionsCheckbox2">Disabled checkbox</label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" id="optionsCheckbox2" value="option1" disabled="">
						This is a disabled checkbox
					</label>
				</div>
			</div>
			<div class="control-group warning">
				<label class="control-label" for="inputWarning">Input with warning</label>
				<div class="controls">
					<input type="text" id="inputWarning">
					<span class="help-inline">Something may have gone wrong</span>
				</div>
			</div>
			<div class="control-group error">
				<label class="control-label" for="inputError">Input with error</label>
				<div class="controls">
					<input type="text" id="inputError">
					<span class="help-inline">Please correct the error</span>
				</div>
			</div>
			<div class="control-group success">
				<label class="control-label" for="inputSuccess">Input with success</label>
				<div class="controls">
					<input type="text" id="inputSuccess">
					<span class="help-inline">Woohoo!</span>
				</div>
			</div>
			<div class="control-group success">
				<label class="control-label" for="selectError">Select with success</label>
				<div class="controls">
					<select id="selectError">
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
					</select>
					<span class="help-inline">Woohoo!</span>
				</div>
			</div>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary">Save changes</button>
				<button class="btn">Cancel</button>
			</div>
		</fieldset>
	</form>

	<h3>OMG so much FANCY</h3>
	<form class="form-horizontal">
		<fieldset>
			<div class="control-group">
				<label class="control-label">Form grid sizes</label>
				<div class="controls docs-input-sizes">
					<input class="span1" type="text" placeholder=".span1">
					<input class="span2" type="text" placeholder=".span2">
					<input class="span3" type="text" placeholder=".span3">
					<select class="span1">
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
					</select>
					<select class="span2">
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
					</select>
					<select class="span3">
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
					</select>
					<p class="help-block">Use the same <code>.span*</code> classes from the grid system for input sizes.</p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Alternate sizes</label>
				<div class="controls docs-input-sizes">
					<input class="input-mini" type="text" placeholder=".input-mini">
					<input class="input-small" type="text" placeholder=".input-small">
					<input class="input-medium" type="text" placeholder=".input-medium">
					<p class="help-block">You may also use static classes that don't map to the grid, adapt to the responsive CSS styles, or account for varying types of controls (e.g., <code>input</code> vs. <code>select</code>).</p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="prependedInput">Prepended text</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on">@</span><input class="span2" id="prependedInput" size="16" type="text">
					</div>
					<p class="help-block">Here's some help text</p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="appendedInput">Appended text</label>
				<div class="controls">
					<div class="input-append">
						<input class="span2" id="appendedInput" size="16" type="text"><span class="add-on">.00</span>
					</div>
					<span class="help-inline">Here's more help text</span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="appendedPrependedInput">Append and prepend</label>
				<div class="controls">
					<div class="input-prepend input-append">
						<span class="add-on">$</span><input class="span2" id="appendedPrependedInput" size="16" type="text"><span class="add-on">.00</span>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="appendedInputButton">Append with button</label>
				<div class="controls">
					<div class="input-append">
						<input class="span2" id="appendedInputButton" size="16" type="text"><button class="btn" type="button">Go!</button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="appendedInputButtons">Two-button append</label>
				<div class="controls">
					<div class="input-append">
						<input class="span2" id="appendedInputButtons" size="16" type="text"><button class="btn" type="button">Search</button><button class="btn" type="button">Options</button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="inlineCheckboxes">Inline checkboxes</label>
				<div class="controls">
					<label class="checkbox inline">
						<input type="checkbox" id="inlineCheckbox1" value="option1"> 1
					</label>
					<label class="checkbox inline">
						<input type="checkbox" id="inlineCheckbox2" value="option2"> 2
					</label>
					<label class="checkbox inline">
						<input type="checkbox" id="inlineCheckbox3" value="option3"> 3
					</label>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="optionsCheckboxList">Checkboxes</label>
				<div class="controls">
					<label class="checkbox">
						<input type="checkbox" name="optionsCheckboxList1" value="option1">
						Option one is this and that—be sure to include why it's great
					</label>
					<label class="checkbox">
						<input type="checkbox" name="optionsCheckboxList2" value="option2">
						Option two can also be checked and included in form results
					</label>
					<label class="checkbox">
						<input type="checkbox" name="optionsCheckboxList3" value="option3">
						Option three can—yes, you guessed it—also be checked and included in form results
					</label>
					<p class="help-block"><strong>Note:</strong> Labels surround all the options for much larger click areas and a more usable form.</p>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Radio buttons</label>
				<div class="controls">
					<label class="radio">
						<input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked="">
						Option one is this and that—be sure to include why it's great
					</label>
					<label class="radio">
						<input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
						Option two can is something else and selecting it will deselect option one
					</label>
				</div>
			</div>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary">Save changes</button>
				<button class="btn">Cancel</button>
			</div>
		</fieldset>
	</form>
{/box}
