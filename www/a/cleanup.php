<?php
chdir("..");
include("inc.php");
require_once('Pager.php');

if( !IsAdmin() )
	die();

if( Request('action') )
{
	switch(Request('action'))
	{
		case 'drawing_name':
			$DB->Query('UPDATE drawing_main SET tagline = "'.mysql_real_escape_string(Request('name')).'" WHERE id = '.Request('drawing_id'));
			$res = $DB->SingleQuery('SELECT tagline FROM drawing_main WHERE id = '.Request('drawing_id'));
			echo '(' . json_encode(array('drawing_id'=>Request('drawing_id'), 'name'=>$res['tagline'])) . ')';
			break;

		case 'skillset':
			$DB->Update('drawing_main', array(
				'skillset_id'=>intval(Request('skillset_id')),
				'program_id'=>0
			), Request('drawing_id'));

			if( Request('skillset_id') )
				$programs_ = $DB->MultiQuery('SELECT * FROM programs WHERE skillset_id='.intval(Request('skillset_id')).' ORDER BY title');
			else
				$programs_ = $DB->MultiQuery('SELECT * FROM programs ORDER BY title');
			
			$programs = array(array('id'=>'0', 'title'=>''));
			foreach( $programs_ as $p )
			{
				$programs[] = array('id'=>$p['id'], 'title'=>$p['title']);
			}
			
			echo '('.json_encode($programs).')';
			break;

		case 'program':
			if( Request('program_id') )
			{
				$skillset = $DB->SingleQuery('SELECT skillset_id FROM programs WHERE id='.intval(Request('program_id')));
			}
			else
			{
				$skillset = array('skillset_id'=>0);
			}

			$DB->Update('drawing_main', array(
				'skillset_id'=>intval($skillset['skillset_id']),
				'program_id'=>Request('program_id')
			), Request('drawing_id'));
		
			echo '('.json_encode(array('skillset_id'=>$skillset['skillset_id'])).')';
			break;
			
		default:
			pa($_REQUEST);
			break;
	}
	die();
}	

PrintHeader();
?>
<script type="text/javascript" src="/common/jquery-1.3.min.js"></script>
<script type="text/javascript" src="/files/jquery.selectboxes.min.js"></script>
<script type="text/javascript">
	var $j = jQuery.noConflict();
</script>
<?php
$total_drawings = $DB->SingleQuery('SELECT COUNT(*) AS num FROM drawing_main');

$params = array(
        'mode'          => 'Sliding',
        'perPage'       => 30,
        'delta'         => 2,
        'totalItems'=> $total_drawings['num'],
        'spacesBeforeSeparator' => 1,
        'spacesAfterSeparator' => 1,
        'urlVar' => 'pg',
        'curPageSpanPre' => '',
        'curPageSpanPost' => '',
        'nextImg' => '&gt;',
        'prevImg' => '&lt;',
        'curPageLinkClassName' => 'active',
        'clearIfVoid' => true,
);
$pager = &Pager::factory($params);
$offset = $pager->getOffsetByPageId();

$drawings = $DB->MultiQuery('
	SELECT m.*, school_abbr
	FROM drawing_main m
	LEFT JOIN schools s ON m.school_id=s.id
	ORDER BY tagline
	LIMIT '.($offset[0]-1).', '.($params['perPage']).'
');



if( $pager->links != "" ) {
        echo '<p><div class="pager_links">'.$pager->links.'</div></p>';
} else {
        echo '<br>';
}

echo '<table>' . "\n";

foreach( $drawings as $i=>$d )
{
	echo '<tr>'; // style="background-color:#'.($i%2==0?'DDDDDD':'FFFFFF').'">';
		echo '<td><a href="/c/published/' . $d['id'] . '/view.htm" target="_blank">' . SilkIcon('magnifier.png') . '</td>';
		echo '<td><a href="/a/drawings.php?action=drawing_info&id=' . $d['id'] . '" target="_blank">' . SilkIcon('cog.png') . '</td>';

		echo '<td>' . $d['school_abbr'] . '</td>';

		echo '<td><input type="text" class="drawing_name" id="drawing_'.$d['id'].'" value="' . htmlspecialchars($d['tagline']) . '" style="width: 220px" /></td>';

		echo '<td><div id="skillset_'.$d['id'].'" class="skillset">';
			echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', $d['skillset_id'], array('0'=>''));
		echo '</div></td>';

		echo '<td><div id="program_'.$d['id'].'" class="program">';
			if( intval($d['skillset_id']) != 0 )
				$where = 'skillset_id = '.$d['skillset_id'];
			else
				$where = '';
			echo GenerateSelectBoxDB('programs', 'program_id', 'id', 'title', 'title', $d['program_id'], array('0'=>''), $where);
		echo '</div></td>';
/*
		echo '<td><div id="olmis_'.$d['id'].'" class="olmis">';

			$versions = $DB->MultiQuery('SELECT * FROM drawings WHERE parent_id='.$d['id']);
	
			$olmis = array();
			foreach( $versions as $v )
			{
				$content = $DB->MultiQuery('SELECT content
					FROM objects
					WHERE drawing_id = ' . $v['id'] . '
					AND content LIKE "%http://www.qualityinfo.org%"');
				foreach( $content as $c )
					// http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ=292011&go=Continue
					if( preg_match_all('|qualityinfo\.org/olmisj/OIC?.*?occ=([0-9]{6})|', $c['content'], $matches) )
						foreach( $matches[1] as $m )
							if( !in_array($m, $olmis) )
							{
								$olmis[] = $m;
							}
			}
			foreach( $olmis as $o )
			{
				echo '<nobr><input type="checkbox" value="'.$o.'" /><a href="http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$o.'&go=Continue">'.$o.'</a></nobr> ';
			}

		echo '</div></td>';
*/
	echo '</tr>';
}

echo '</table>';
?>
<script type="text/javascript">
$j(document).ready(function(){

	$j(".drawing_name").change(function(){
		var drawing_id = $j(this).attr("id").split("_")[1];
		$j.get("cleanup.php",
			{action: "drawing_name",
			 drawing_id: $j(this).attr("id").split("_")[1],
			 name: $j(this).val()},
			 function(data){
			 	json = eval(data);
			 	if( json == null )
			 	{
				 	$j("#drawing_"+drawing_id).css({backgroundColor: "#FFAAAA"});
				}
				else
				{
				 	$j("#drawing_"+drawing_id).val(json.name);
					blinkGreen("#drawing_"+drawing_id+" select");
				}
			 }
		)
	
	});

	$j('.skillset select').bind('change', function() {
		var drawing_id = $j(this).parent().attr("id").split("_")[1];
		$j(this).css({backgroundColor: '#FFFFAA'});
		$j.post('cleanup.php',
			{action: 'skillset',
			 drawing_id: $j(this).parent().attr("id").split("_")[1],
			 skillset_id: $j(this).val()
			},
			function(data) {
				json = eval(data);
				if( json == null )
				{

				}
				else
				{
					programList = json;
					loadProgramTitles(drawing_id);
					blinkGreen("#skillset_"+drawing_id+" select");
				}
			}
		);
	});

	$j('.program select').bind('change', function() {
		var drawing_id = $j(this).parent().attr("id").split("_")[1];
		$j(this).css({backgroundColor: '#FFFFAA'});
		$j.post('cleanup.php',
			{action: 'program',
			 drawing_id: drawing_id,
			 program_id: $j(this).val()
			},
			function(data) {
				json = eval(data);
				if( json == null )
				{

				}
				else
				{
					$j('#skillset_'+drawing_id+' select').val(json.skillset_id);
					blinkGreen("#program_"+drawing_id+" select");
				}
			}
		);
	});


});

function loadProgramTitles(id) {
	$j("#program_"+id+" select").removeOption(/./);
	for( var i=0; i<programList.length; i++ )
	{
		$j("#program_"+id+" select").addOption(programList[i].id, programList[i].title).val(0);
	}
}

function blinkGreen(id)
{
 	$j(id).css({backgroundColor: "#AAFFAA"});
 	setTimeout(function(){
	 	$j(id).css({backgroundColor: "#FFFFFF"});
 	}, 300);
}

</script>
<?php
PrintFooter();
?>
