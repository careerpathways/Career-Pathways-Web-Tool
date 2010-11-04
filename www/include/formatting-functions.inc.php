<?php


function ShowLoginForm($email="") {
global $SITE;

	if( $SITE->force_https_login() ) {
		$form_action = "https://".$SITE->https_server().':'.$SITE->https_port()."/a/login.php";
	} else {
		$form_action = "/a/login.php";
	}

	?>
	<div style="margin-right:20px">
	<?php ShowBrowserNotice(); ?>
	</div>

	<br /><br />
	<form action="<?= $form_action; ?>" method="post">
	<table align="center">
	<tr>
		<td>Email:</td>
		<td><input type="text" size="20" name="email" id="email" value="<?= $email; ?>"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" size="20" name="password" id="password"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Log In" class="submit"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><br/><br/><span class="button_link"><a href="/a/guestlogin.php">Guest Login</a></span></td>
	</tr>
	</table>

	<input type="hidden" name="next" value="<?= (Request('next')) ?>">
	</form>

	<?php
	echo str_repeat('<br/>',20);
}




function showPublishForm($mode)
{
	global $DB;
	$version_id = intval($_REQUEST['version_id']);
	if( $mode == 'post' )
		$version = $DB->SingleQuery('SELECT * FROM post_drawings WHERE id='.$version_id);
	else
		$version = $DB->SingleQuery('SELECT * FROM drawings WHERE id='.$version_id);
	
	?>
	<div style="border: 1px solid rgb(119, 119, 119); margin-left: 15px; margin-right: 15px; background-color: white; padding: 15px;">
	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
		<p>Are you sure you want to <?=$version['published'] == 0?'':'un'?>publish this version? 
			<?= $version['published'] == 0 ? 
				'Any web pages that are embedding this drawing will automatically be updated to this version.' :
				'This drawing will no longer be visible in any web pages that embed it.' ?>
		</p>
		<input type="submit" class="submit" value="Yes" />
		<input type="button" class="submit" value="No" onclick="chGreybox.close()" />

		<input type="hidden" name="action" value="<?=$version['published'] == 0?'':'un'?>publish" />
		<input type="hidden" name="drawing_id" value="<?=$version_id?>" />

		<?php
		if( $version['frozen'] == 0 ) {
			echo '<br /><br /><p>Note: You can lock this version instead. This will prevent anyone from making changes to this version, but will not update websites that have embedded this drawing.</p>';
		}
		?>

	</form>
	</div>
	<?php
}


function ShowOlmisCheckboxes($drawing_id, $defaultChecked=false, $text='', $readonly=false)
{
	global $DB;
	$html = '';
	if( is_array($drawing_id) )
	{
		if( count($drawing_id) > 0 )
		{
			$olmis = $DB->MultiQuery('SELECT olmis_id, job_title AS title
				FROM olmis_codes
				WHERE olmis_id IN ('.implode(',',$drawing_id).')
				ORDER BY title');
			foreach( $olmis as $o )
			{
				$html .= '<div id="olmischk_'.$o['olmis_id'].'">';
				if(!$readonly)
					$html .= '<input type="checkbox" id="olmis_'.$o['olmis_id'].'" '.($defaultChecked?'checked="checked"':'').'/> ';
				$html .= '<a href="http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$o['olmis_id'].'&go=Continue" target="_blank">'.$o['title'].'</a></div>';
			}
		}
	}
	else
	{
		$olmis = $DB->MultiQuery('SELECT l.*, IFNULL(c.job_title, l.olmis_id) AS title
			FROM olmis_links AS l
			LEFT JOIN olmis_codes AS c on l.olmis_id = c.olmis_id
			WHERE drawing_id = '.$drawing_id.'
			ORDER BY title');
		if( count($olmis) > 0 )
		{
			$html .= '<div style="">' . $text . '</div>';
		}
		foreach( $olmis as $o )
		{
			$link = '<a href="http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$o['olmis_id'].'&go=Continue" target="_blank">';
			$html .= '<div id="olmischk_'.$o['olmis_id'].'">';
			if(!$readonly)
				$html .= '<input type="checkbox" id="olmis_'.$o['olmis_id'].'" checked="checked" /> ';
			$html .= $link . '<img src="/images/olmis-16.gif" /></a> ' . $link . $o['title'] . '</a></div>';
		}
	}
	return $html;
}

function SearchForOLMISLinks($content)
{
	global $DB;
	
	// 1. parse $content for olmis urls
	// 2. for any ids that are not in the database, parse the olmis page to find the job title
	// 3. return an array of IDs
	
	$soc = array();
	if( preg_match_all('~(qualityinfo\.org|olmis\.emp\.state\.or\.us)/olmisj/OIC?.*?occ=([0-9]{6})~', $content, $matches) )
	foreach( $matches[2] as $m )
		if( !in_array($m, $soc) )
		{
			$soc[] = $m;
		}

	foreach( $soc as $s )
	{
		$query = $DB->SingleQuery('SELECT * FROM olmis_codes WHERE olmis_id = "'.$s.'"');
		if( !is_array($query) )
		{
			$content = file_get_contents('http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$s.'&go=Continue');
			if( preg_match('|"reportSubTitle">for ([^0-9\(\)]+) \(|', $content, $match) )
			{
				$title = $match[1];
				$DB->Insert('olmis_codes', array('olmis_id'=>$s, 'job_title'=>$title));
			}
		}
	}

	return $soc;	
}


function ShowBrowserNotice()
{
	$browser = $_SERVER['HTTP_USER_AGENT'];
	$notice = '';

	if( preg_match('~Firefox/(1|2)~', $browser) )
		$notice = "You appear to be using an old version of Firefox. We recommend you upgrade to the <a href=\"http://www.mozilla.com/firefox/\">latest version of Firefox</a> in order to have full access to the web tool.";

	if( preg_match('~Firefox~', $browser) == 0 )
		$notice = "We recommend using <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> for the best experience with the web tool.";

	if( preg_match('~MSIE (6|5)~', $browser) )
		$notice = "Internet Explorer 6 is not supported by this website. Most features should work, but you may experience glitches. To avoid this, we recommend using the latest version of <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> or <a href=\"http://www.microsoft.com/windows/Internet-explorer/default.aspx\">Internet Explorer</a>";

	if( preg_match('~MSIE 7~', $browser) )
		$notice = "You appear to be using Internet Explorer. We recommend you use <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> for the best experience with the web tool.";

	if( $notice != '' )
	{
	?>
	<div id="browserNotice">
		<div style="float:left;"><a href="http://www.mozilla.com/firefox/"><img src="/images/firefox-logo.png" /></a></div>
		<div style="margin-left:100px;"><?= $notice ?></div>
	</div>
	<div style="clear:right"></div>
	<?php
	}
}


function ShowRoadmapHeader($drawing_id)
{
	global $DB;
	$drawing = $DB->SingleQuery('SELECT school_abbr,
		IF(m.name="", p.title, m.name) AS full_name
		FROM drawing_main AS m
		JOIN schools ON m.school_id=schools.id
		LEFT JOIN programs AS p ON m.program_id=p.id
		WHERE m.id = '.$drawing_id);
	return '<img src="/files/titles/' . base64_encode($drawing['school_abbr']) . '/' . base64_encode($drawing['full_name']) . '.png" alt="' . $drawing['school_abbr'] . ': ' . $drawing['full_name'] . '" height="19" width="800" />';
}


function ShowPostHeader($drawing_id)
{
	global $DB;
	$drawing = $DB->SingleQuery('SELECT school_abbr,
		IF(m.name="", p.title, m.name) AS full_name
		FROM post_drawing_main AS m
		JOIN schools ON m.school_id=schools.id
		LEFT JOIN programs AS p ON m.program_id=p.id
		WHERE m.id = '.$drawing_id);
	return '<img src="/files/titles/' . base64_encode($drawing['school_abbr']) . '/' . base64_encode($drawing['full_name']) . '.png" alt="' . $drawing['school_abbr'] . ': ' . $drawing['full_name'] . '" height="19" width="800" />';
}

function ShowPostViewHeader($view_id)
{
	global $DB;
	$view = $DB->SingleQuery('SELECT name FROM vpost_views WHERE id = ' . $view_id);
	return '<img src="/files/titles/post/' . base64_encode('-') . '/' . base64_encode($view['name']) . '.png" alt="' . $view['name'] . '" width="800" height="19" />';
}

function ShowDrawingList(&$mains, $type='pathways') {
	global $DB;

	switch( $type )
	{
		case 'pathways':
			$draw_page = 'drawings.php';
			break;
		case 'post':
			$draw_page = 'post_drawings.php';
			break;		
	}

	if( count($mains) == 0 ) {
		echo '<p>(none)</p>';
	} else {
		echo '<table width="100%">';
		echo '<tr>';
			echo '<th colspan="4">Occupation/Program</th>';
			echo '<th width="240">Last Modified</th>';
			echo '<th width="240">Created</th>';
			//echo '<th width="40">SVG</th>';

		foreach( $mains as $mparent ) {
			echo '<tr class="drawing_main">';

			echo '<td><a href="'.$draw_page.'?action=drawing_info&id='.$mparent['id'].'" class="edit"><img src="/common/silk/cog.png" width="16" height="16" title="Drawing Properties" /></a></td>';
			echo '<td colspan="3" class="drawinglist_name">'.$mparent['name'].'</td>';
			$created = ($mparent['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$mparent['created_by']));
			$modified = ($mparent['last_modified_by']==array('name'=>'')?"":$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$mparent['last_modified_by']));
			echo '<td><span class="fwfont">'.($mparent['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$mparent['last_modified'])).'</span> <a href="/a/users.php?id='.$mparent['last_modified_by'].'">'.$modified['name'].'</a></td>';
			echo '<td><span class="fwfont">'.($mparent['date_created']==''?'':$DB->Date('Y-m-d f:i a',$mparent['date_created'])).'</span> <a href="/a/users.php?id='.$mparent['created_by'].'">'.$created['name'].'</a></td>';

			foreach( $mparent['drawings'] as $dr ) {
				echo '<tr class="'.($dr['published']==1?'published':'').'">';
					$drawViewText = 'Draw/Edit Version';
					if( CanEditVersion($dr['id'], $type) ) {
						if( $dr['published'] == 1 || $dr['frozen'] == 1 ) {
							$linktext = SilkIcon('picture.png');
						} else {
							$linktext = SilkIcon('pencil.png');
						}
					} else {
						$linktext = SilkIcon('picture.png');
						$drawViewText = 'View Version';
					}

					echo '<td width="30">&nbsp;</td>';

					echo '<td width="160">';
						echo 'Version '.$dr['version_num'].' ';
						if( $dr['published'] == 1 )
							echo '<img src="/common/silk/report.png" width="16" height="16" title="Published Version" />';
						echo (!array_key_exists('note',$dr) || $dr['note']==''?"":' ('.$dr['note'].')');
					echo '</td>';

					echo '<td width="70">';
						echo '<a href="'.$draw_page.'?action=version_info&amp;version_id='.$dr['id'].'" class="edit" title="Version Settings">'.SilkIcon('wrench.png').'</a>';
					echo '</td>';

					echo '<td width="70">';
						echo '<a href="'.$draw_page.'?action=draw&amp;version_id='.$dr['id'].'" class="edit" title="'.$drawViewText.'">'.$linktext.'</a>';
					echo '</td>';


					$created = ($dr['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$dr['created_by']));
					$modified = ($dr['last_modified_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$dr['last_modified_by']));
					echo '<td><span class="fwfont">'.($dr['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$dr['last_modified'])).'</span> <a href="/a/users.php?id='.$dr['last_modified_by'].'">'.$modified['name'].'</a></div></td>';
					echo '<td><span class="fwfont">'.($dr['date_created']==''?'':$DB->Date('Y-m-d f:i a',$dr['date_created'])).'</span> <a href="/a/users.php?id='.$dr['created_by'].'">'.$created['name'].'</a></td>';

					//echo '<td>';
					//	echo '<a href="/files/charts/svg/'.$dr['id'].'.svg"><img src="/images/svg_icon.png" width="16" height="12"></a>';
					//echo '</td>';
				echo '</tr>';
			}

			echo '</tr>';
		}


		echo '</tr>';
		echo '</table>';
	}

}

function ShowSmallDrawingConnectionList($drawing_id, $type=null, $links=array())
{
	global $DB;
	
	$connections = $DB->MultiQuery('SELECT post_id, tab_name, sort
		FROM vpost_links AS v
		JOIN post_drawing_main AS d ON v.post_id=d.id
		WHERE type="'.$type.'" AND vid='.$drawing_id.' ORDER BY sort');
	if( count($connections) == 0 )
	{
		echo '(none)';
		return;
	}
	echo '<table>';
	echo '<tr>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="280">Occupation/Program</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th>Tab Name</th>';
		echo '<th>Sort</th>';
		echo '<th width="180">Organization</th>';
		echo '<th width="285">Last Modified</th>';
	echo '</tr>';
	foreach( $connections as $c )
	{
		$d = $DB->SingleQuery('SELECT M.*, CONCAT(U.first_name," ",U.last_name) AS modified_by, schools.school_name
			FROM post_drawing_main M
			JOIN post_drawings D ON D.parent_id=M.id
			LEFT JOIN users U ON M.last_modified_by=U.id
			LEFT JOIN schools ON M.school_id=schools.id
			WHERE M.id='.intval($c['post_id']).'
			ORDER BY name');

		echo '<tr>';
			echo '<td><a href="'.str_replace('%%', $d['id'], $links['delete']).'">' . SilkIcon('cross.png') . '</a></td>';
			echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $d['id'] . '">' . SilkIcon('cog.png') . '</a></td>';
			echo '<td>' . $d['name'] . '</td>';
			echo '<td><a href="javascript:preview_drawing(\''.$d['code'].'\')" title="View Version">' . SilkIcon('magnifier.png') . '</a></td>';
			echo '<td width="90">';
				echo '<input type="text" id="tabName_'.$c['post_id'].'" class="tabName tabID_'.$c['post_id'].'" value="' . $c['tab_name'] . '" style="width:90px" />';
			echo '</td>';
			echo '<td width="60">';
				echo '<input type="text" id="tabSort_'.$c['post_id'].'" class="tabSort tabID_'.$c['post_id'].'" value="' . $c['sort'] . '" style="width:20px" />';
				echo '<input type="button" class="tabNameBtn" id="tabNameBtn_'.$c['post_id'].'" style="width:30px;font-size:9px;margin-left:2px;" value="Save" />';
			echo '</td>';
			echo '<td>' . $d['school_name'] . '</td>';
			echo '<td><span class="fwfont">'.($d['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$d['last_modified'])).'</span> ' . $d['modified_by'] . '</td>';
		echo '</tr>';
	}
	echo '</table>';
	
}

