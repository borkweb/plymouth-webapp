<div id="ape_notes" class="ape-section {if $myuser->go_states.ape_notes === '0'}ape-section-hidden{/if}">
	<h3>Student Notes</h3>
	<ul class="apedata">
		{foreach from=$person->student->ug->notes item=item}
			<li>
				<h4><label>Date: </label>{$item.activity_date|date_format:"%b %e, %Y %I:%m %p"}</h4>
				<h4><label>Term Code: </label>{$item.term_code}</h4>
				<p>
				<h4><label>Notes: </label>{$item.comment_text|link_urls|replace:'’':"'"|replace:'¿':"'"|replace:'—':'`'|replace:'”':'"'|replace:'“':'"'|nl2br}
				</p>
			</li>
		{foreachelse}
			<li class="apenoresults">No Information Available</li>
		{/foreach}
	</ul>
	<form action="{$PHP.BASE_URL}/actions/add_new_note.php}?pidm={$person->pidm}&term_code={$person->student->ug->term_code}" method="POST">
		<textarea name="note" rows="5" cols="80">{$post.note|escape}</textarea>
		<input type="submit" value="submit" />
	</form>
</div>
