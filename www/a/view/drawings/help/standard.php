<?php
$text = $DB->SingleQuery('SELECT * FROM news WHERE category="help" AND sort_index=1001');
echo $text['text'];
?>

<script type="text/javascript">
var showHelp = function(anchor) {
	window.open('/a/help_popup.php' + ((typeof anchor != "undefined") ? "#" + anchor : ""), 'Help', 'menubar=no,scrollbars=yes,width=350,height=600,screenX=100,screenY=100');
};
</script>
<p><img src="/common/silk/help.png" alt=""/> <a href="javascript:showHelp()">More Help</a></p>