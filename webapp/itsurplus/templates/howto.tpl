{box size="16" title="Managing Surplus"}
	<ul>
		<li><a href="#retrieval">How Surplus is Retrieved</a></li>
		<li><a href="#pricing">Surplus Pricing</a></li>
		<li><a href="#condition">Surplus Condition</a></li>
		<li><a href="#messages">Creating and Managing Messages</a></li>
		<li><a href="#image">Adding an Image</a></li>
	</ul>
{/box}
{box size="16" title="How Surplus is Retrieved" id="retrieval"}
	<p>All surplus items are pulled from GLPI. To do this, the surplus application looks at computers, 
	peripherals, and network equipment grabbing anything that has had it's status set to Surplused. The 
	following information is retrieved, but not necessarily displayed:</p>
	<ul>
		<li>GLPI ID Number</li>
		<li>PSU Name</li>
		<li>Serial Number</li>
		<li>Notes</li>
		<li>Model</li>
		<li>Manufacturer</li>
		<li>Item Type (eg. Laptop, Desktop, etc...)</li>
		<li>Price</li>
		<li>Condition</li>
	</ul>
	<p>It is good to note that if warranty information is not enabled for a surplus item, then the item will not show 
	up o surplus. This information is required to determine the items price and condition.</p>
{/box}
{box size="16" title="Surplus Pricing" id="pricing"}
	<p>Pricing policy will be determined by the computer shop. Once determined, an item's price should be placed 
	in the Warranty Value field to be retrieved and displayed on the surplus site.</p>
{/box}
{box size="16" title="Surplus Condition" id="condition"}
	<p>An item can be listed with one of the following three conditions: Good, Fair, and Poor. This value should be 
	stored in the Warranty Info field for an item. This will then be rendered in Surplus. As a note, if no 
	condition is enterred, Surplus will interpret NULL values as having a condition of Good.</p>
{/box}
{box size="16" title="Creating and Managing Messages" id="messages"}
	<p>Surplus messages are managed through the Tools &gt Notes area of GLPI. To create a new message, simply create a 
	new Note with a title in the format "Surplus - " followed by some description. Next, set the note to Public. The 
	content that you write in the Text area is what will be displayed in the messages area of Surplus.</p>
	<p>As an additional feature, notes can be scheduled to start in the future, or even run for a duration of time.</p>
{/box}
{box size="16" title="Adding an Image" id="image"}
	<p>Since Surplus handles items primarily as groups of a certain model of item, images are stored in a different 
	way, not associated with any particular item. Instead, and image should be uploaded via the Management &gt 
	Documents area. To have an image associated with an item model, simply upload the image, titling it as the 
	model that you are trying to associate. Please note that for an association to occur, the title must be enterred 
	exactly as the model appears.</p>
{/box}
