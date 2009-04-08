<?php
$TEMPLATE->toolbar_function = 'ShowDrawingListHelp';

PrintHeader();
?>

<script type="text/javascript">
	var MODE = '<?= $MODE ?>';
</script>

<script type="text/javascript" src="/files/drawing_list.js?mode=<?= $MODE ?>"></script>
<script type="text/javascript" src="/files/prototype.js"></script>

<table width="100%"><tr>
<td>
<?php
	if( $MODE == 'post' )
	{
		$my_type = $DB->GetValue('organization_type', 'schools', intval($_SESSION['school_id']));
		if( IsAdmin() ) {
			?>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=cc" class="edit"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new cc pathway</span></a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="/a/post_drawings.php?action=new_drawing_form&type=hs" class="edit"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new hs program</span></a>
			<?php
		}
		elseif( $my_type == 'HS' )
		{
			?>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=hs" class="edit"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new drawing</span></a>
			<?php
		}
		else
		{
			?>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=cc" class="edit"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new cc pathway</span></a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a href="/a/post_drawings.php?action=new_drawing_form&type=hs" class="edit"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new hs program</span></a>
			<?php
		}
		?>
		&nbsp;&nbsp;&nbsp;&nbsp;<a href="/a/post_import.php" class="edit"><img src="/common/silk/lightning_go.png" width="16" height="16"> <span class="imglinkadjust">import drawing</span></a>
		<?php
	}
	else
	{
		?>
		<a href="/a/drawings.php?action=new_drawing_form" class="edit"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new drawing</span></a>
		<?php
	}
	?>
	&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:selectDefaults()" class="edit"><img src="/common/silk/user.png" width="16" height="16"> <span class="imglinkadjust">my drawings</span></a>
</td>
<td width="290">
	<div id="search_form" align="right">
		<input type="text" size="20" name="search_box" id="search_box" value="<?= Request('search') ?>">
		<input type="button" class="submit" value="Search" onclick="do_search()">
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
		<div class="title">Occupations/Programs</div>
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