<?php
$TEMPLATE->toolbar_function = 'ShowDrawingListHelp';

PrintHeader();
?>

<script type="text/javascript" src="/files/drawing_list.js"></script>
<script type="text/javascript" src="/files/prototype.js"></script>

<table width="100%"><tr>
<td>
	<a href="<?= $_SERVER['PHP_SELF'] ?>?action=new_drawing_form" class="edit"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new drawing</span></a>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<a href="javascript:selectDefaults()" class="edit"><img src="/common/silk/user.png" width="16" height="16"> <span class="imglinkadjust">my drawings</span></a>
</td>
<td width="290">
	<div id="search_form" align="right">
		Keyword Search
		<input type="text" size="20" name="search_box" id="search_box" value="<?= Request('search') ?>">
		<input type="button" class="submit" value="Go" onclick="do_search()">
	</div>
</td>
</tr></table>

<table id="live_lists"><tr>
<td width="33%">
	<div class="live_list">
		<div class="title">Organizations</div>
		<select size="13" id="list_schools" multiple onchange="queue_change(this)">
		</select>
	</div>
</td>
<td width="33%">
	<div class="live_list">
		<div class="title">Users</div>
		<select size="13" id="list_people" multiple onchange="queue_change(this)">
		</select>
	</div>
</td>
<td width="33%">
	<div class="live_list">
		<div class="title">Titles</div>
		<select size="13" id="list_categories" multiple onchange="queue_change(this)">
		</select>
	</div>
</td>
</tr></table>

<div id="drawing_list"></div>

<script type="text/javascript">
	init();
</script>

<?php
PrintFooter();