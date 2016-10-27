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
	/* chview.js will updated chartcontainer with more specific size. 
	 * These have to be big for initial rendering to take place. */
	margin-top: 20px;
	height: 1600px;
	width: 1200px;
	overflow:hidden;
}
</style>
<script type="text/javascript">
<?php require('chart_data_js.php'); ?>
</script>

<div class="title_img"><?= ShowRoadmapHeader($drawing['parent_id']) ?></div>
	<div class="drawing-info">
		<?php 
			if($drawing['published'] && $drawing_main['show_pdf_ada_links']){
				$show_pdf_ada_links = true;
			} else {
				$show_pdf_ada_links = false;
			}
		?>
		<?php if($drawing['show_updated']): ?>
		<?php $last_modified_time = strtotime($drawing['last_modified']); ?>
		<div class="last_modified" style="float: right;font-size:8pt;font-weight:bold;">
			Last Updated: <?= date('n-j-Y', $last_modified_time) ?>
		</div>
		<?php endif; ?>
		<br>
		<br>
		<?php if($show_pdf_ada_links): ?>
		<div id="alt-links" style="float: right;font-size:8pt;">
			<?php
			$schls_query = "SELECT * FROM schools WHERE organization_type IN ('CC', 'Other') ORDER BY school_name";
			$schls = $DB->VerticalQuery($schls_query,'school_abbr','id');

			$accessible_url = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/$$/%%.html';    
			$accessible_url = str_replace(
				array('$$','%%'),
				array(
					$drawing['parent_id'], 
					CleanDrawingCode($schls[$drawing['school_id']].'-'.$drawing['full_name'])
				),
				$accessible_url
			);

			$pdf_url = 'http://'.$_SERVER['SERVER_NAME'].'/pdf/$$/%%.pdf';
			$pdf_url = str_replace(
				array('$$','%%'),
				array(
					$drawing['parent_id'],
					CleanDrawingCode(
						GetDrawingName($drawing['parent_id'], 'roadmap')
					)
				),
				$pdf_url
			);
			$pdf_link = '<a target="_blank" href="' . $pdf_url . '"><i class="fa fa-file-pdf-o"></i>Printable PDF</a>';
			$accessible_link = '<a target="_blank" href="'.$accessible_url.'"><i class="fa fa-file-text-o"></i>Text-Only</a>';
			echo $pdf_link . '|' . $accessible_link;
			?>

		</div>
		<?php endif; ?>
	</div>
<div class="title_skillset" style="font-size:8pt;font-weight:bold;position:absolute;top:22px;">
	<?= l('skillset name')?>: <?= $drawing['skillset'] ?>
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
