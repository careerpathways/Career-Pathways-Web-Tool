<?php
chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

$drawings = $DB->MultiQuery('SELECT d.*, school_name, school_abbr, v.name AS view_name, version.id AS version_id
	FROM vpost_views AS v
	JOIN vpost_links AS vl ON v.id = vl.vid
	JOIN post_drawing_main AS d ON vl.post_id=d.id
	JOIN post_drawings AS version ON version.parent_id=d.id
	JOIN schools AS s ON d.school_id=s.id
	WHERE v.code = "'.Request('code').'"
		AND published = 1');

$page_title = 'Not Found';

$hs = array();
$cc = array();
foreach( $drawings as $d )
{
	if( $d['type'] == 'CC' )
		$cc[] = $d;
	else
		$hs[] = $d;
	
	$page_title = $d['view_name'];
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?= $page_title ?></title>
	<link rel="stylesheet" href="/c/pstyle.css" />
	<script type="text/javascript" src="/files/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="/files/js/jquery.ui.core.js"></script>
	<script type="text/javascript" src="/files/js/jquery.ui.tabs.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#tabshs").tabs();
			$("#tabscc").tabs();
		});
	</script>
	<link rel="stylesheet" href="/files/js/jquery/ui.tabs.css" />
	<link rel="stylesheet" href="/files/js/jquery/ui.all.css" />
</head>
<body>

<?php
foreach( array('hs'=>$hs, 'cc'=>$cc) as $type=>$ds )
{
	if( count($ds) == 0 )
	{
		
	}
	elseif( count($ds) == 1 )
	{
		$p = POSTChart::create($d['version_id']);
		$p->display();
	}
	else
	{
	?>
	<div id="tabs<?=$type?>">
		<ul>
			<?php
			foreach( $ds as $i=>$d )
			{
				$school_name = str_replace(array(' High School', ' Community College'), '', $d['school_name']);
				echo '<li><a href="#tabs'.$type.'-'.($i+1).'">' . $school_name . '</a></li>';
			}
			?>
		</ul>
		<?php
		foreach( $ds as $i=>$d )
		{
			echo '<div id="tabs'.$type.'-'.($i+1).'">';
			$p = POSTChart::create($d['version_id']);
			$p->display();
			echo '</div>';
		}
		?>

	</div>
	<?php
	}
}
?>

</body>
</html>