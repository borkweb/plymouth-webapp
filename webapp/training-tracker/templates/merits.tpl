{PSU_JS src="../js/merit_editor.js"}
{box title="Idk some title here... Yea...." size = "16"}
	<button class='btn add-new'>Add new</button>
	<button class='btn remove-old'>Remove</button>
	<form action='/webapp/training-tracker/staff/merit' name='new-merit' method='post'>
		<div class='hidden additional-info new'>
				Add a new /type/
				<textarea rows="10" cols="50">text</textarea>
				<button>Add</button>
		</div>
		<div class='hidden additional-info remove'>
			Choose which ones you care to remove.
			Some list with check boxes, asking which ones you wanna delete.
				<button>remove</button>
		</div>
	</form>
	
{/box}
