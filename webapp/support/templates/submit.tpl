{box title="<img src='/images/icons/22x22/apps/accessories-text-editor.png'/> Create A Ticket" class="grid_16"}
<form id="new_call" action="/webapp/calllog/add_new_call.html?call_source=support&amp;redirect={$PHP.BASE_URL|@urlencode}" enctype="multipart/form-data" method="post">
{$form}
<br/>
<button type="submit">Submit Ticket</button>
<div class="clear"></div>
</form>
{/box}
