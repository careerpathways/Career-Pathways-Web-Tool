<style type="text/css">
@import '/c/chstyle.css';
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
<!--[if lt IE 9]><script type="text/javascript" src="/files/excanvas.js"></script><![endif]-->
<?php } else { ?>
<!--[if lt IE 9]>
<script type="text/javascript" src="/files/flashcanvas.js"></script>
<![endif]-->
<?php } ?>
<script type="text/javascript" src="/files/prototype.js"></script>
<script type="text/javascript" src="/c/chview.js"></script>
