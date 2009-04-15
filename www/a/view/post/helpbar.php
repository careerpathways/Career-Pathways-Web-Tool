<div id="helpbar">
	<div id="helpbar_header"></div>
	<div id="helpbar_content">
		<?php
			global $DB;
			$text = $DB->SingleQuery('SELECT * FROM news WHERE category="help" AND sort_index=2001');
			echo $text['text'];
		?>
		<p><img src="/common/silk/help.png" alt=""/> <a href="javascript:showHelp()">More Help</a></p>
	</div>
</div>
<script type="text/javascript">
var showHelp = function(anchor) {
	window.open('/a/help_popup.php?post' + ((typeof anchor != "undefined") ? "#" + anchor : ""), 'Help', 'menubar=no,scrollbars=yes,width=350,height=600,screenX=100,screenY=100');
};
</script>
