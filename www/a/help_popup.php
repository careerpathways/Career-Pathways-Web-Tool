<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Help &bull; Pathways</title>
<style type="text/css">@import "/styles.css";</style>
<style type="text/css">
body {
	padding: 1em;
}
ul {
	margin-top: 2px;
	padding-left: 20px;
}
dt {
	font-weight: bold;
}

dd {
	margin-bottom: 1em;
}
</style>
</head>
<body>
	<h1>Help</h1>

	<h2>General</h2>
	<ul>
		<li>Right-click the drawing canvas for a tools menu to access grid settings, or create new objects.</li>
		<li>Right-click an object for a menu of customizing options.</li>
		<li>Change an object's color by selecting it and clicking a color from the <var>TOOLS</var> color palette, or using <var>Color</var> on the menu.</li>
		<li>To delete an object, first select it, then hit the <kbd>DELETE</kbd> key on your keyboard, or select <var>Delete</var> from the object's menu.</li>
	</ul>

	<h2>Drawing Canvas Menu</h2>
	<b>New Menu</b>
	<ul>
		<li>Adds a new <b>box</b>, <b>line</b>, or <b>arrow</b> to the drawing canvas in the same location that was right-clicked..</li>
	</ul>
	<b>Grid</b> &rarr; <b>Show Grid</b>
	<ul>
		<li>Turns the show grid feature on or off.</li>
		<li>Default is set to on.</li>
	</ul>
	<b>Grid</b> &rarr; <b>Snap to Grid</b>
	<ul>
		<li>Turns the snap to grid feature on or off.</li>
		<li>Default is set to on. </li>
		<li>Objects snap to nearest horizontal/vertical grid axis. </li>
	</ul>
	<b>Grid</b> &rarr; <b>Edit</b>
	<ul>
		<li>Opens a pop-up window to edit the grid size of drawing canvas.</li>
		<li>Default is set to 10.</li>
		<li>Type any number to manually change grid size, e.g. 15, 20, 25, etc. </li>
	</ul>
	<b>Changes to grid settings are not saved, and return to default settings, upon exiting the drawing.</b>
	<br /><br />

	<h2>Box Menu</h2>
	Right-click the drawing canvas and select <b>New</b> &rarr; <b>Box</b> to add a new box object. The box will be added in the same location that was right-clicked.<br />
	<br />
	Right-click box objects to access:<br />
	<br />
	<b>Edit Content</b>
	<ul>
		<li>Opens the box's content editor in a pop-up window.</li>
	</ul>
	<b>Edit Title</b>
	<ul>
		<li>Enables an in-place text editor for the box's title.</li>
	</ul>
	<b>Color Menu</b>
	<ul>
		<li>Sets the color of the box.</li>
	</ul>
	<b>Start Connection Here/End Connection Here</b>
	<ul>
		<li>Draws an arrow between two selected boxes. </li>
		<li>Right-click the starting box to select "Start Connection Here"; right-click the ending box to select "End Connection Here" from the menu.</li>
		<li>Creates a dynamic connection that moves with the connected boxes as layout changes occur. </li>
		<li>Right-click connection for menu of customizing options.</li>
	</ul>

	<h2>Connection Menu</h2>
	Please use Connections for ease of layout adjustments and ADA compliance. Connections can translate the relationship of box objects in text only views of these drawings.<br />
	<br />
	Right-click the starting box to select "Start Connection Here"; right-click the ending box to select "End Connection Here" from the menu.<br />
	Creates a dynamic connection that moves with the connected boxes as layout changes occur.<br />
	<br />
	Right-click box connections to access:<br />
	<br />
	<b>Start Point menu</b>
	<ul>
		<li>Sets the side of the box the line is drawn from: Top, Bottom, Left or Right.</li>
	</ul>
	<b>End Point menu</b>
	<ul>
		<li>Sets the side of the box the line is drawn to: Top, Bottom, Left or Right.</li>
	</ul>
	<b>Orientation menu</b>
	<ul>
		<li>Sets the orientation (horizontal or vertical) of the connection from the start point. This option is not available if segment is set to 1 (Direct Line).</li>
	</ul>
	<b>Segments menu</b>
	<ul>
		<li>Sets the number of segments in the connecting line:</li>
		<ul>
			<li>1-Seg Line (Straight)</li>
			<li>1-Seg Line (Diagonal)</li>
			<li>2-Seg Line ("L")</li>
			<li>3-Seg Line</li>
		</ul>
		<li>The default, 1-segment line, may not be visually connected to the end point. Adjust by moving the ending box, or selecting a different segment or orientation option.</li>
		<li>1-Seg Line (Diagonal) creates a diagonal line to the destination box regardless of box position.</li>
	</ul>
	<b>Color menu</b>
	<ul>
		<li>Sets the color of the connection.</li>
	</ul>
	<b>Auto Position</b>
	<ul>
		<li>Detects box location to adjoining box, and resets connection to 1-Seg Line (Straight).</li>
	</ul>
	<b>Delete</b>
	<ul>
		<li>Permanently removes the connection, after confirmation. </li>
	</ul>

	<h2>Line and Arrow Menu</h2>
	Lines and arrows must be manually aligned with box objects, and are discouraged. Please use Connections for ease of layout adjustments and ADA compliance. Connections can translate the relationship of box objects in text only views of these drawings.<br />
	<br />
	Right-click line and arrow objects to access:<br />
	<br />
	<b>Color Menu</b>
	<ul>
		<li>Sets the color of the line or arrow.</li>
	</ul>
	<b>Duplicate</b>
	<ul>
		<li>Makes an exact copy of the line or arrow.</li>
		<li>Offsets new duplicate below original.</li>
	</ul>
	<b>Delete</b>
	<ul>
		<li>Permanently removes the line or arrow, after confirmation. </li>
	</ul>

	<h2>Positioning Objects</h2>
	<ul>
		<li>When selected, boxes, connections, lines and arrows show a blue frame, with a green control point at the start, and a red control point at the end. The object's menu can be brought up by right-clicking anywhere within this frame.</li>
		<li>Click and drag the green start point or red end point to vertically or horizontally adjust the starting or ending location.</li>
		<li>Connection start and end points can "slide" to any desired location along the <var>TOP</var>, <var>BOTTOM</var>, <var>LEFT</var> and <var>RIGHT</var> edges of box objects.</li>
		<li>When dragging a box, line or arrow, hold down the <kbd>ALT</kbd> key (<kbd>Option</kbd> on Mac) to move the object without snapping to the grid.</li>
		<li>When dragging a start or end point for a line or arrow, hold down the <kbd>Shift</kbd> key to make the line snap to a vertical or horizontal layout.</li>
	</ul>

	<h2>Resizing Boxes</h2>
	<ul>
		<li>When selected, boxes are highlighted with a blue frame, with left and right blue control points. Click and drag either point to expand/retract box width.</li>
		<li>Box height is determined by content.</li>
	</ul>

	<h2>Editing Box Content</h2>
	DO NOT copy and paste text from MS Word. Unpredictable glitches can occur and damage drawings.<br />
	<br />

	<h2>Locking Versions</h2>
	<ul>
		<li>If not ready to publish a drawing (publicly accessible), click <b>(ADD ICON)</b> <var>LOCK</var> this version from the <var>TOOLS</var> menu to prevent changes to a specific version.</li>
		<li>Copy it to a new version to make changes.</li>
	</ul>

	<h2>Copy This Version</h2>
	From the <var>TOOLS<var> menu, click <b>(ADD ICON)</b> copy this version. A pop-up window will appear.<br />
	<br />
	<ul>
		<li><b><i>Your Organization</i></b></li>
		<ul>
			<li>Create a <b>New Version</b> to the existing drawing</li>
			<ul>
				<li>The next available version number will be added in your drawing list.</li>
			</ul>
			<li>Create a <b>New Drawing</b> copies the existing drawing into an identical new drawing at your organization, and Version 1 will be created.</li>
			<ul>
				<li>Edit the new drawing name (Occupation/Program) before hitting “OK”. </li>
			</ul>
		</ul>
		<li><b><i>Another Organization</i></b></li>
		<ul>
			<li>A <b>New Drawing</b> will be created in your organization.</li>
			<li>Edit the new drawing name (Occupation/Program) before hitting “OK”. </li>
			<ul>
				<li>The drawing will be added to your drawings list and Version 1 will be created.</li>
			</ul>
		</ul>
	</ul>
	The drawing canvas will refresh with the new version or drawing.<br />
	<br />
	<b><i>NOTE:</i></b> If no new drawing name is provided, the original drawing name is used and the word “copy” is appended to the new drawing name by default.<br />
	<br />

	<h2>Print This Version</h2>
	From the <var>TOOLS</var> menu, click <b>(ADD ICON)</b> print this version to render a Roadmap Drawing for printing from the browser menu. <br />
	<ul>
		<li>Set your browsers print settings to "print background images" so that titles of boxes print white over dark colored boxes.</li>
		<li>Select <b>File</b> &rarro; <b>print preview</b> to preview your drawing prior to printing, and to ensure it will fit to <b>one (1) page</b>. </li>
		<li>Change the print size/scale from "Shrink to Fit" to a fixed percentage if the preview shows an undesired layout.</li>
	</ul>
	<b><i>NOTE:</i></b> For best results, design your Roadmap Drawing to fit to one (1) page in a portrait layout, by keeping all objects within the boundaries of the drawing title bar. The Web Tool does not currently support landscape layout or multi-page printing.<br />
	<br />

	<h2>Publishing Drawings</h2>
	If ready to publish a drawing in a publicly accessible website, click <b>(ADD ICON)</b> publish this version from the TOOLS menu. This will mark the drawing as published, and prevent changes to the "published" version.
Copy it to a new version to make changes. New versions that are published will be instantly accessible through shared published links.

</body>
</html>