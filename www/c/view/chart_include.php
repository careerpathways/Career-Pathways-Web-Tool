<?php //DO NOT remove the getBaseUrl() as this file may be accessed from the outside. ?>
<style type="text/css">
@import '<?= getBaseUrl() ?>/c/chstyle.css';
.chVDivider { 
	background: #ccc;
	height: 100%;
	left: 800px;
	position: absolute;
	top: 0px;
	width: 3px;
	z-index: -1; 
}
.chHDivider { 
	background: #ccc;
	height: 3px;
	left: 0;
	position: absolute;
	top: 980px;
	width: 800px;
	z-index: -1; 
}
#chartcontainer {
	/* chview.js will updated chartcontainer with more specific size. These have to be big for initial rendering to take place. */
	height: 1600px;
	width: 1200px;
	overflow:hidden;
}
</style>
<script type="text/javascript">
<?php require('chart_data_js.php'); ?>
</script>


<div class="title_img"><?= ShowRoadmapHeader($drawing['parent_id']) ?></div>
<?php if($drawing['show_updated']): ?>
	<?php $last_modified_time = strtotime($drawing['last_modified']); ?>
	<div class="last_modified" style="float: right;font-size:8pt;font-weight:bold;">Last Updated: <?= date('n-j-Y', $last_modified_time) ?></div>
<?php endif; ?>
<div class="title_skillset" style="font-size:8pt;font-weight:bold;"><?= l('skillset name')?>: <?= $drawing['skillset'] ?></div>
<div id="alt-links">
<?php
$schls = $DB->VerticalQuery("SELECT * FROM schools WHERE organization_type IN ('CC', 'Other') ORDER BY school_name",'school_abbr','id');
$accessible_url = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/$$/%%.html';    
$accessible_url = str_replace(array('$$','%%'),array($drawing['id'],CleanDrawingCode($schls[$drawing['school_id']].'-'.$drawing['full_name'])),$accessible_url);

$testVar = GetDrawingName($drawing['id'], 'roadmap');
var_dump($testVar);

$pdf_url = 'http://'.$_SERVER['SERVER_NAME'].'/pdf/$$/%%.pdf';
$pdf_url = str_replace(
	array('$$','%%'),
	array(
		$drawing['id'],
		CleanDrawingCode(
			GetDrawingName($drawing['id'], 'roadmap')
		)
	),
	$pdf_url);

var_dump($drawing);
?>

	<a href="<?= $accessible_url ?>">Text-Only</a>
	 | 
	<a href="http://oregon.ctepathways.org/pdf/<?= $drawing['id']?>/program.pdf">Printable PDF</a>
</div>


<div id="chartcontainer" style="position:relative;"><!-- chview.js will draw the chart here --></div>



<?php if(isset($_GET['action'])){ ?>
<!--[if lt IE 9]><script type="text/javascript" src="<?= getBaseUrl() ?>/files/excanvas.js"></script><![endif]-->
<?php } else { ?>
<!--[if lt IE 9]>
<script type="text/javascript" src="<?= getBaseUrl() ?>/files/flashcanvas.js"></script>
<![endif]-->
<?php } ?>
<script type="text/javascript" src="<?= getBaseUrl() ?>/files/prototype.js"></script>
<script type="text/javascript" src="<?= getBaseUrl() ?>/c/chview.js"></script>
