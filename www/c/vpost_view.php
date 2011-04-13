<?php
chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

$drawings = $DB->MultiQuery('SELECT d.*, school_name, school_abbr, v.name AS view_name, version.id AS version_id, tab_name, skillset_id
	FROM vpost_views AS v
	JOIN vpost_links AS vl ON v.id = vl.vid
	JOIN post_drawing_main AS d ON vl.post_id=d.id
	JOIN post_drawings AS version ON version.parent_id=d.id
	JOIN schools AS s ON d.school_id=s.id
	WHERE v.id = '.intval(Request('id')).'
		AND version.published = 1
	ORDER BY vl.sort, vl.tab_name');

$page_title = 'Not Found';

$hs = array();
$cc = array();
$skillsets = array();
foreach( $drawings as $d )
{
	if( $d['skillset_id'] != '' )
	{
		if( !array_key_exists($d['skillset_id'], $skillsets) )
			$skillsets[$d['skillset_id']] = 0;
		$skillsets[$d['skillset_id']]++;
	}
	if( $d['type'] == 'CC' )
		$cc[] = $d;
	else
		$hs[] = $d;
	
	$page_title = $d['school_name'] . ' - Plan of Study - ' . $d['view_name'];
}

if( Request('format') == 'html' )
{

	if(count($drawings) == 0)
		drawing_not_found('postview', Request('id'));
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?= $page_title ?></title>
	<script type="text/javascript" src="/files/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="/files/js/jquery.ui.core.js"></script>
	<script type="text/javascript" src="/files/js/jquery.ui.tabs.js"></script>
	<link rel="stylesheet" href="/files/js/jquery/ui.tabs.css" />
	<link rel="stylesheet" href="/files/js/jquery/ui.all.css" />
	<link rel="stylesheet" href="/c/pstyle.css" />
	<link rel="stylesheet" href="/c/pstyle-print.css"<?=(array_key_exists('print', $_GET) ? '' : ' media="print"')?> />
<?php
	if(!array_key_exists('print', $_GET))
	{
?>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#tabshs").tabs();
			$("#tabscc").tabs();
		});
	</script>
<?php
	}
?>
</head>
<body>

<?php

echo '<div style="margin-bottom: 10px">';
echo '<div id="post_title">';
	echo ShowPostViewHeader(intval(Request('id')));
echo '</div>';
/*
if( count($skillsets) > 0 )
{
	asort($skillsets);
	$skillsets = array_flip($skillsets);
	$skillset = $DB->SingleQuery('SELECT title FROM oregon_skillsets WHERE id = '.array_pop($skillsets));

	echo '<div id="skillset">';
		echo l('skillset name') . ': ' . $skillset['title'];
	echo '</div>';
}
*/
echo '</div>';

foreach( array('hs'=>$hs, 'cc'=>$cc) as $type=>$ds )
{
	echo '<div style="margin-bottom:10px;">';
	if( count($ds) == 0 )
	{
		
	}
	elseif( count($ds) == 1 )
	{
		try
		{
			$p = POSTChart::create($ds[0]['version_id']);
			$p->display();
		}
		catch( Exception $e )
		{
			echo '<div class="error">Drawing not found</div>';
		}
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
				echo '<li><a href="#tabs'.$type.'-'.($i+1).'">' . $d['tab_name'] . '</a></li>';
			}
			?>
		</ul>
		<?php
		foreach( $ds as $i=>$d )
		{
			echo '<div id="tabs'.$type.'-'.($i+1).'" class="tabs_'.$type.'">';
			try
			{
				$p = POSTChart::create($d['version_id']);
				$p->display($d['tab_name']);
			}
			catch( Exception $e )
			{
				echo '<div class="error">Drawing not found</div>';
			}
			echo '</div>';
		}
		?>

	</div>
	<?php
	}
	echo '</div>';
}

if(array_key_exists('print', $_GET)) {
	echo '<div class="footnote">Printed on ' . date('n/j/Y g:ia') . '</div>';
}

include('view/course_description_include.php');
?>
<script type="text/javascript">
	$(function(){
		$("#tabshs").bind("tabsshow", function(e, ui){
			$(ui.panel).find(".post_cell .cell_container").each(function(){
				if($(this).find("img").length > 0) {
					var h = $(this).parent(".post_cell").height();
					$(this).css({
						height: h + "px"
					});
				}
			});
		});
	});
</script>

</body>
</html>
<?php


}
elseif( Request('format') == 'js' )
{
		header("Content-type: text/javascript");
?>
		var s=document.createElement('script');
		s.setAttribute('src','http://<?=$_SERVER['SERVER_NAME']?>/c/log/post/<?=$_REQUEST['id']?>?url='+window.location);
		document.getElementsByTagName('body')[0].appendChild(s);

		var pc = document.getElementById("<?=(Request('container')?Request('container'):'postContainer')?>");
		if( typeof VBArray != "undefined" ) {
			var fr = document.createElement('<iframe src="http://<?=$_SERVER['SERVER_NAME']?>/c/study/<?=$_REQUEST['id']?>/embed.html" width="'+pc.style.width+'" height="'+pc.style.height+'" frameborder="0" scrolling="no"></iframe>');
		} else {
			var fr = document.createElement('iframe');
			fr.setAttribute("width", pc.style.width);
			fr.setAttribute("height", pc.style.height);
			fr.setAttribute("src", "http://<?=$_SERVER['SERVER_NAME']?>/c/study/<?=$_REQUEST['id']?>/embed.html");
			fr.setAttribute("frameborder", "0");
			fr.setAttribute("scrolling", "auto");
		}
		document.getElementById('postContainer').appendChild(fr);
<?php
	
}

?>
