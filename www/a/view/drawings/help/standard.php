<a href="javascript:void(0);" onclick="return showHelp('GeneralFeatures');">General Features</a><br />
<a href="javascript:void(0);" onclick="return showHelp('AddingObjects');">Adding Objects</a><br />
<a href="javascript:void(0);" onclick="return showHelp('EditingBoxContents');">Editing Box Content</a><br />
<a href="javascript:void(0);" onclick="return showHelp('DeletingObjects');">Deleting Objects</a><br />
<a href="javascript:void(0);" onclick="return showHelp('DuplicatingObjects');">Duplicating Objects</a><br />
<a href="javascript:void(0);" onclick="return showHelp('ConnectingBoxes');">Connecting Boxes</a><br />
<a href="javascript:void(0);" onclick="return showHelp('PositioningObjects');">Positioning Objects</a><br />
<a href="javascript:void(0);" onclick="return showHelp('ResizingBoxes');">Resizing Boxes</a><br />
<br />
<a href="javascript:void(0);" onclick="return showHelp('LockingVersions');">Locking Versions</a><br />
<a href="javascript:void(0);" onclick="return showHelp('CopyingVersions');">Copying Versions</a><br />
<a href="javascript:void(0);" onclick="return showHelp('PrintingVersions');">Printing Versions</a><br />
<a href="javascript:void(0);" onclick="return showHelp('PublishingDrawings');">Publishing Drawings</a><br />

<script type="text/javascript">
var showHelp = function(anchor) {
	window.open('/a/help_popup.php' + ((typeof anchor != "undefined") ? "#" + anchor : ""), 'Help', 'menubar=no,scrollbars=yes,width=350,height=600,screenX=100,screenY=100');
	return false;
	
};
</script>
<p><img src="/common/silk/help.png" alt=""/> <a href="/a/help_popup.php" onclick="return showHelp()">More Help</a></p>