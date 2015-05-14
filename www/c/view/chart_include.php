<?php //DO NOT remove the SERVER_NAME as this file may be class from the outside. ?>
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
</style>
<script type="text/javascript">
<?php require('chart_data_js.php'); ?>
</script>

<?php if(isset($_GET['action'])){ ?>
<!--[if lt IE 9]><script type="text/javascript" src="<?= getBaseUrl() ?>/files/excanvas.js"></script><![endif]-->
<?php } else { ?>
<!--[if lt IE 9]>
<script type="text/javascript" src="<?= getBaseUrl() ?>/files/flashcanvas.js"></script>
<![endif]-->
<?php } ?>
<script type="text/javascript" src="<?= getBaseUrl() ?>/files/prototype.js"></script>
<script type="text/javascript" src="<?= getBaseUrl() ?>/c/chview.js"></script>
