<script type="text/javascript">
	$(apejs.distauthz_init);
</script>
<div id="authz-management">
{box title="Permission and Role Management"}
	<p>Create, Edit, and Delete Roles, Permissions...etc:</p>

	<div class="datacolumns" id="distauthz">
		<div id="col1" class="datacolumn">
			<div class="filter">
				<h2>Attributes</h2>
				<label>Filter:</label><input type="text" />
			</div>
      <h3>Role</h3>
			<ul id="alter">
				{foreach from=$authz item=attribute}
					{if $attribute.type_id == 2}
						<li class="child click" ><a rel="{$attribute.attribute}"><label>{$attribute.name}</label></a></li>
					{/if}
				{/foreach}
			</ul>
      <h3>Permission</h3>
			<ul>
				{foreach from=$authz item=attribute}
					{if $attribute.type_id == 1}
						<li class="child click" ><a rel="{$attribute.attribute}"><label>{$attribute.name}</label></a></li>
					{/if}
				{/foreach}
			</ul>
		</div>
		<div id="col2" class="datacolumn roles-perms">
			<h2 id="col2_header"></h2>
			<h3 style="display:none;" >Child Roles</h3>
			<div style="display:none;">
				<span class="left">Default</span>
				<span class="right">Delete</span>
			</div>
			<div style="clear:both;"></div>
			<ul>
			</ul>
			<h3 style="display:none;" >Child Permissions</h3>
			<div style="display:none;">
				<span class="left">Default</span>
				<span class="right">Delete</span>
			</div>
			<div style="clear:both;"></div>
			<ul>
			</ul>
		</div>
		<div id="col3" class="datacolumn possible-roles-perms datacolumnnolist datacolumnlast">
			<div class="filter" style="display:none;">
				<h2>Add Children</h2>
				<label>Filter:</label><input type="text" />
			</div>
      <h3 style="display:none;">Role</h3>
			<ul>
			</ul>
      <h3 style="display:none;">Permission</h3>
			<ul>
			</ul>
		</div>
		<div class="clear">&nbsp;</div>
	</div>
{/box}
{box title="Permission and Role Management Management"}
	<form id="attribute_info" class="label-left">
    <h2>Description <a>new</a></h2>
    <ul>
  		<li>
				<label>Name:</label>
				<input type="text" value="" id="attr_name" />
			</li>
			<li>
				<label>Slug:</label>
				<input type="text" id="attr_slug" value="" />
      </li>
			<li>
				<label>Type:</label>
				<select id="attr_type">
          <option></option>
					<option value="1">Permission</option>
					<option value="2">Role</option>
				</select>
			</li>
  	  <li>
        <label>Description:</label>
				<textarea id="description"></textarea>
			</li>
			<li>
				<h3>Meta: <a>add</a></h3>
				<ul id="meta"></ul>
			</li>
			<li>
				<input type="submit" value="Do it" />
			</li>
		</ul>
  </form>
{/box}
<div id="new_child" style="display:none;">
	<li>
		<input class="child_info" name="{$child.parent_attribute}--" type="checkbox" />
		<div class="child_name"></div>
		<a style="float:right;">[x]</a>
	</li>
</div>
<div id="new_meta" style="display:none;">
	<li><input type="text" name="meta[]" value="" /></li>
</div>
<div id="new_add" style="display:none;">
	<li class="child click" ><a class="add_attr"><label class="add_name"></label></a></li>
</div>
</div>
