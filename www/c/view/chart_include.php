<style type="text/css">
@import 'http://<?= $_SERVER['SERVER_NAME'] ?>/c/chstyle.css';
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

<?php /* <!--[if lte IE 8]><script type="text/javascript" src="http://<?= $_SERVER['SERVER_NAME'] ?>/files/excanvas.js"></script><![endif]--> */ ?>
<!--[if lt IE 9]>
<script type="text/javascript" src="http://<?= $_SERVER['SERVER_NAME'] ?>/files/flashcanvas.js"></script>
<![endif]-->
<script type="text/javascript" src="http://<?= $_SERVER['SERVER_NAME'] ?>/files/prototype.js"></script>
<script type="text/javascript" src="http://<?= $_SERVER['SERVER_NAME'] ?>/c/chview.js"></script>
