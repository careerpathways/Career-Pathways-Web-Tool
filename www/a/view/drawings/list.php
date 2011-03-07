<?php
$TEMPLATE->toolbar_function = 'ShowDrawingListHelp';

PrintHeader();
?>

<script type="text/javascript" src="/common/jquery-1.3.min.js"></script>
<script type="text/javascript">
	jQuery.noConflict();
	var MODE = '<?= $MODE ?>';
</script>

<script type="text/javascript" src="/files/drawing_list.js?mode=<?= $MODE ?>"></script>
<script type="text/javascript" src="/files/prototype.js"></script>

<table width="100%"><tr>
<td><nobr>
<?php
	if( $MODE == 'post' )
	{
		$my_type = $DB->GetValue('organization_type', 'schools', intval($_SESSION['school_id']));
		if( IsAdmin() ) {
			?>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=cc" class="edit dlist_link"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new cc pathway</span></a>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=hs" class="edit dlist_link"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new hs program</span></a>
			<?php
		}
		elseif( $my_type == 'HS' )
		{
			?>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=hs" class="edit dlist_link"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new drawing</span></a>
			<?php
		}
		else
		{
			?>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=cc" class="edit dlist_link"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new cc pathway</span></a>
			<a href="/a/post_drawings.php?action=new_drawing_form&type=hs" class="edit dlist_link"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new hs program</span></a>
			<?php
		}
		?>
		<a href="/a/post_import.php" class="edit dlist_link"><img src="/common/silk/lightning_go.png" width="16" height="16"> <span class="imglinkadjust">import drawing</span></a>
		<?php
	}
	else
	{
		if( IsStaff() ) {
		?>
		<a href="/a/drawings.php?action=new_drawing_form" class="edit dlist_link"><img src="/common/silk/add.png" width="16" height="16"> <span class="imglinkadjust">new drawing</span></a>
		<?php
		}
	}
	if( $MODE == 'post' || ($MODE != 'post' && IsStaff()) ) {
	?>
	<a href="javascript:selectDefaults()" class="edit dlist_link"><img src="/common/silk/user.png" width="16" height="16"> <span class="imglinkadjust">my drawings</span></a>
	<?php 
	}
	      if( $MODE == 'post' ) { ?>
		<a href="javascript:selectDefaultsGrp()" class="edit dlist_link"><img src="/common/silk/group.png" width="16" height="16"> <span class="imglinkadjust">affiliated drawings</span></a>
	<?php } ?>
</nobr></td>
<td width="240">
	<div id="search_form" align="right"><nobr>
		<input type="text" size="20" name="search_box" id="search_box" value="<?= (Request('search') ? Request('search') : Session('drawing_list_search_' . $MODE)) ?>">
		<input type="button" class="submit" value="Search" onclick="do_search()">
		
		<input type="button" class="submit" value="Reset" onclick="do_reset()">
		
	</nobr></div>
</td>
</tr></table>

<table id="live_lists"><tr>
<td width="33%">
	<div class="live_list">
		<div class="ajaxloader"></div>
		<div class="title">Organizations</div>
		<select size="13" id="list_schools" multiple onchange="queue_change(this)">
		</select>
	</div>
</td>
<td width="33%">
	<div class="live_list">
		<div class="ajaxloader"></div>
		<div class="title">Users</div>
		<select size="13" id="list_people" multiple onchange="queue_change(this)">
		</select>
	</div>
</td>
<td width="33%">
	<div class="live_list">
		<div class="ajaxloader"></div>
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
