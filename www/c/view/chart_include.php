<?php //DO NOT remove the getBaseUrl() as this file may be accessed from the outside.?>
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
.title_skillset {
    font-size:8pt;
    font-weight:bold;
    float:left;
}
.drawing-info {
    font-size:8pt;
    font-weight: bold;
    padding: 0 3px;
    text-align:right;
}
.drawing-info a {
    color: #001133;
    text-decoration: none;
}
</style>
<?php
$schls_query = "SELECT * FROM schools WHERE organization_type IN ('CC', 'Other') ORDER BY school_name";
$schls = $DB->VerticalQuery($schls_query, 'school_abbr', 'id');

$accessible_url = getBaseUrl().'/c/text/$$/%%.html';
$accessible_url = str_replace(
    array('$$', '%%'),
    array(
        $drawing['parent_id'],
        CleanDrawingCode($schls[$drawing['school_id']].'-'.$drawing['full_name']),
    ),
    $accessible_url
);

$pdf_url = getBaseUrl().'/pdf/$$/%%.pdf';
$pdf_url = str_replace(
    array('$$', '%%'),
    array(
        $drawing['parent_id'],
        CleanDrawingCode(
            GetDrawingName($drawing['parent_id'], 'roadmap')
        ),
    ),
    $pdf_url
);
?>
<script type="text/javascript">
<?php require 'chart_data_js.php'; ?>
</script>

<div class="title_img"><?= ShowRoadmapHeader($drawing['parent_id']) ?></div>

<div class="drawing-info">
    <div class="title_skillset">
        <?= l('skillset name')?>: <?= $drawing['skillset'] ?>
    </div>

    <?php if ($drawing['show_updated']): ?>
        <?php $last_modified_time = strtotime($drawing['last_modified']); ?>
        <div class="last_modified">
            Last Updated: <?= date('n-j-Y', $last_modified_time) ?>
        </div>
    <?php endif; ?>

    <?php if ($drawing['show_pdf_ada_links']): ?>
    <div class="alt-links">
        <a target="_blank" href="<?= $pdf_url ?>"><i class="fa fa-file-pdf-o"></i> Printable PDF</a>
        |
        <a target="_blank" href="<?= $accessible_url ?>"><i class="fa fa-file-text-o"></i> Text-Only</a>
    </div>
    <?php endif; ?>
</div>

<div id="chartcontainer" style="position:relative;"><!-- chview.js will draw the chart here --></div>

<?php if (isset($_GET['action'])) {
    ?>
    <!--[if lt IE 9]><script type="text/javascript" src="<?= getBaseUrl() ?>/files/excanvas.js"></script><![endif]-->
    <?php
} else {
    ?>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="<?= getBaseUrl() ?>/files/flashcanvas.js"></script>
    <![endif]-->
    <?php
} ?>
<script type="text/javascript" src="<?= getBaseUrl() ?>/files/prototype.js"></script>
<script type="text/javascript" src="<?= getBaseUrl() ?>/c/chview.js"></script>
