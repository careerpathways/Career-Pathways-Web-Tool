<?php
include("simple_html_dom.php");

function ShowLoginForm($email="")
{
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

function BuildOlmisLink($socCode)
{
    return "https://www.qualityinfo.org/jc-oprof/?at=1&t1={$socCode}~{$socCode}~4101000000~0";
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
                $url = BuildOlmisLink($o['olmis_id']);
				$html .= '<a href="'.$url.'">'.$o['olmis_id'].' - '.$o['title'].'</a></div>';
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
            $url = BuildOlmisLink($o['olmis_id']);
			$link = '<a href="'.$url.'" target="_blank">';
			$html .= '<div id="olmischk_'.$o['olmis_id'].'">';
			if(!$readonly)
				$html .= '<input type="checkbox" id="olmis_'.$o['olmis_id'].'" checked="checked" /> ';
			$html .= $link . '<img src="/images/olmis-16.gif" /></a> ' . $link . $o['olmis_id'].' - ' . $o['title'] . '</a></div>';
		}
	}
	return $html;
}

function SearchForOLMISLinks($content)
{
	global $DB;

	// 1. parse $content for olmis urls
	// 2. for any ids that are not in the database, parse the drawing content to
    //    find the job title.
	// 3. return an array of IDs
	//e.g. https://www.qualityinfo.org/jc-oprof/?at=1&amp;t1=434171~434171~4101000000~0
	$soc = array();
	if( preg_match_all('/(qualityinfo\.org|olmis\.emp\.state\.or\.us)[^"]*t1=[^~]+~([0-9]{6})~/', $content, $matches) )
	foreach( $matches[2] as $m ) {
		if( !in_array($m, $soc) )
		{
			$soc[] = $m;
		}
    }

    // Add all OLMIS titles that don't yet exist in the olmis_codes table.
    // #118325745 - take OLMIS titles from drawing content, rather than
    // quality info website.
	foreach( $soc as $s )
	{
		$query = $DB->SingleQuery('SELECT * FROM olmis_codes WHERE olmis_id = "'.$s.'"');
		if( !is_array($query) )
		{
            // Didn't find this OLMIS id in the db, let's try and add it.
            try {
                $_c = unserialize($content);
                if(isset($_c['config']['content'])){
                    $c = $_c['config']['content'];
                    $html = new simple_html_dom();
                    $html->load($c);
                    $anchors = $html->find('a');
                    foreach ($anchors as $a) {
                        // If OLMIS Id is found in href value, this is a new
                        // OLMIS title that needs to be added to the olmis_codes
                        // table in the db.
                        if (strstr($a->getAttribute('href'), $s)) {
                            // Strip any HTML tags (span, etc)
                            $str = strip_tags($a->innertext);
                            // Replace spaces, tabs and newlines
                            // Credit: "Cez" on http://stackoverflow.com/questions/2326125/remove-multiple-whitespaces
                            $title = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $str);
                            // Add to db
                            $DB->Insert('olmis_codes', array('olmis_id'=>$s, 'job_title'=>$title));
                        }
                    }
                }
            } catch(Exception $e) {
                // TODO log error
                echo $e->getMessage();
            }
		}
	}

	return $soc;
}

function ShowBrowserNotice()
{
	$browser = $_SERVER['HTTP_USER_AGENT'];
	$notice = '';

	if( preg_match('~Firefox/(10|9)~', $browser) )
		$notice = "You appear to be using an old version of Firefox. We recommend you upgrade to the <a href=\"http://www.mozilla.com/firefox/\">latest version of Firefox</a> or <a href=\"https://www.google.com/chrome\">Google Chrome</a> in order to have full access to the Web Tool.";

	if( preg_match('~Firefox~', $browser) == 0 )
		$notice = "We recommend using <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> or <a href=\"https://www.google.com/chrome\">Google Chrome</a> for the best experience with the Web Tool.";

	if( preg_match('~MSIE (6|5)~', $browser) )
		$notice = "Internet Explorer 6 is not supported by this website. Most features should work, but you may experience glitches. To avoid this, we recommend using the latest version of <a href=\"http://www.mozilla.com/firefox/\">Firefox</a>, <a href=\"http://www.microsoft.com/windows/Internet-explorer/default.aspx\">Internet Explorer</a>, or <a href=\"https://www.google.com/chrome\">Google Chrome</a>.";

	if( preg_match('~MSIE 7~', $browser) )
		$notice = "You appear to be using Internet Explorer. We recommend you use <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> or <a href=\"https://www.google.com/chrome\">Google Chrome</a> for the best experience with the Web Tool.";

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

/**
 * Show Roadmap Drawing Header
 * @param mixed int or string - Id of the drawing
 * @return  string HTML for the Roadmap Drawing Header
 */
function ShowRoadmapHeader($drawing_id)
{
	global $DB;
	$name_to_use = GetDrawingName($drawing_id, 'roadmap');
	$school_abbr = GetSchoolAbbr($drawing_id, 'roadmap');
	return _BuildDrawingHeader($school_abbr, l('drawing head pathways 1'), l('drawing head pathways 2'), $name_to_use);
}

/**
 * Show POST Drawing Header
 * @param mixed int or string - Id of the drawing
 * @return  string HTML for the POST Drawing Header
 */
function ShowPostHeader($drawing_id)
{
	global $DB;
	$name_to_use = GetDrawingName($drawing_id, 'post');
	$school_abbr = GetSchoolAbbr($drawing_id, 'post');
	return _BuildDrawingHeader($school_abbr, l('drawing head pathways 1'), l('drawing head pathways 2'), $name_to_use);
	//Use "drawing head pathways..." for now, to mimic live behavior. (e.g. "Career Pathways" instead of "Plan of Study" on POST drawing headers)
	//return _BuildDrawingHeader($school_abbr, l('drawing head post 1'), l('drawing head post 2'), $name_to_use);
}

/**
 * Show "View" Header
 * @param mixed int or string - Id of the view
 * @return  string HTML for the Roadmap Drawing Header
 */
function ShowViewHeader($view_id)
{
	global $DB;
	$name_to_use = GetDrawingName($view_id, 'post_views');
	return _BuildDrawingHeader(null, l('drawing head post 1'), l('drawing head post 2'), $name_to_use);
}

/**
 * Generic function to create drawing header markup.
 * Use ShowRoadmapHeader or ShowPostHeader instead.
 *
 * @return string
 */
function _BuildDrawingHeader($school_abbr = null, $head1, $head2, $name_to_use)
{
	$str = '<div class="drawing-header">'
			. '<span class="column left">';
				$school_abbr ? ($str .= '<span class="school">' . $school_abbr . '</span> ') : '';
				$str .= '<span class="header-1">' . $head1 . '</span>'
				. '<span class="header-2">' . $head2 . '</span>'
			. '</span>'
			. '<span class="column right">'
				. '<span class="title">' . $name_to_use . '</span>'
			. '</span>'
		. '</div>'
		. '
			<script type="application/javascript">
				window.jQuery || document.write(\'<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"><\/script>\')
			</script>
			<script type="application/javascript">
				(function($){
					$("body").bind("drawingheaderchanged", function() {
				        var title_img_w = $(".drawing-header").outerWidth(),
				            l_col_w = $(".left").outerWidth(),
				            r_col_w = $(".right").outerWidth(),
				            both_col_w = l_col_w + r_col_w;

				        if(both_col_w > title_img_w){
				          $(".drawing-header").css("display", "table");
				          $(".column").css("display", "table-cell");
				        }
				    });
					$("body").trigger("drawingheaderchanged");
				}(jQuery));
			</script>
		';
	return $str;
}

/**
 * Get the abbreviation for a drawing's school.
 *
 * @param [type] $drawing_id   string or int - Main drawing id.
 * @param string $drawing_type type of drawing to lookup, either 'roadmap' or 'post'
 * @return string The school abbreviation
 */
function GetSchoolAbbr($drawing_id, $drawing_type)
{
	global $DB;
	$drawing_id = intval($drawing_id);
	if($drawing_type === 'roadmap'){
		$table = 'drawing_main';
	} elseif( $drawing_type === 'post'){
		$table = 'post_drawing_main';
	} else {
		throw new Exception(__FILE__ . ' - Invalid drawing type specificed to ' . __FUNCTION__ . ': ' . $drawing_type);
		return '';
	}
	$drawing = $DB->SingleQuery('SELECT school_abbr
		FROM ' . $table . ' AS m
		JOIN schools ON m.school_id=schools.id

		WHERE m.id = '.$drawing_id);
	return $drawing['school_abbr'];
}

/**
 * Get the name for a drawing based on ID and type.
 *
 * @param mixed $drawing_id  string or int - Main drawing id.
 * @param string $drawing_type type of drawing to lookup, either 'roadmap' or 'post' or 'post_views'
 * @return string The proper title for the drawing.
 */
function GetDrawingName($drawing_id, $drawing_type)
{
	global $DB;
	$drawing_id = intval($drawing_id);
	if($drawing_type === 'roadmap'){
		$table = 'drawing_main';
	} elseif( $drawing_type === 'post_views'){
		$table = 'vpost_views';
	} elseif( $drawing_type === 'post'){
		$table = 'post_drawing_main';
	} else {
		throw new Exception(__FILE__ . ' - Invalid drawing type specificed to ' . __FUNCTION__ . ': ' . $drawing_type);
		return '';
	}
	$drawing = $DB->SingleQuery('SELECT
		m.name AS alternate_title,
		p.title AS approved_program_name
		FROM ' . $table . ' AS m
		JOIN schools ON m.school_id=schools.id
		LEFT JOIN programs AS p ON m.program_id=p.id
		WHERE m.id = '.$drawing_id);

	if(strlen($drawing['approved_program_name']) && !strlen($drawing['alternate_title'])){
		return $drawing['approved_program_name'];
	} else {
		return $drawing['alternate_title'];
	}
}

/**
 * Get Degree Type (a.k.a sidebar_text_right) for a POST Drawing.
 * @param  int $drawing_main_id
 * @return string The degree type.
 */
function GetDegreeType($drawing_main_id)
{
	global $DB;
	$drawing_main_id = intval($drawing_main_id);
	$res = $DB->SingleQuery('SELECT sidebar_text_right
		FROM post_drawings
		WHERE parent_id=' . $drawing_main_id
		. ' AND published=1
		 ORDER BY last_modified DESC'); //this might conflict with /a/post_views.php order by name. See near $degreeTypeConditionQuery
	if(is_array($res) && isset($res['sidebar_text_right'])){
		return $res['sidebar_text_right'];
	} else {
		return null;
	}
}

/**
 * Get Degree Type Abbreviation.
 * @param  string $degreeType The name of the degree type (post_sidebar_options "text" column).
 *                e.g. "One-Year Certificate of Completion" or "High School Diploma"
 * @param  boolean $fallback If true, use the full $degreeType instead of the abbreviation, if no abbreviation is found.
 * @return string The degree type abbreviation.
 */
function GetDegreeTypeAbbr($degreeType, $fallback = true)
{
	global $DB;
	$res = $DB->SingleQuery('SELECT abbreviation
		FROM post_sidebar_options
		WHERE `text`="'. $degreeType .'"');
	if(isset($res['abbreviation']) && strlen($res['abbreviation']) > 0){
		return $res['abbreviation'];
	} else {
		if($fallback){
			return $degreeType; //couldn't find abbreviation
		} else {
			return '';
		}
	}
}

function strnatcmpDrawingName($a,$b)
{
	return strnatcmp($a['DrawingName'],$b['DrawingName']);
}

/**
 * @deprecated Use ShowViewHeader instead.
 */
function ShowPostViewHeader($view_id)
{
	global $DB;
	$view = $DB->SingleQuery('SELECT name FROM vpost_views WHERE id = ' . $view_id);
	return '<img src="/files/titles/post/' . base64_encode('-') . '/' . base64_encode($view['name']) . '.png" alt="' . $view['name'] . '" width="800" height="19" />';
}

function ShowDrawingList(&$mains, $type='pathways')
{
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
			echo '<th colspan="5">Occupation/Program</th>';
			echo '<th width="240">Last Modified</th>';
			echo '<th width="240">Created</th>';
			//echo '<th width="40">SVG</th>';

		foreach( $mains as $mparent ) {
			echo '<tr class="drawing_main">';

			echo '<td><a href="'.$draw_page.'?action=drawing_info&id='.$mparent['id'].'" class="edit"><img src="/common/silk/cog.png" width="16" height="16" title="Drawing Properties" /></a></td>';
			echo '<td colspan="4" class="drawinglist_name">'.$mparent['name'].'</td>';
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

					$degreeType = $dr['sidebar_text_right'];
					echo '<td width="240">';
						echo $degreeType;
					echo '</td>';

					echo '<td width="50">';
						echo '<a href="'.$draw_page.'?action=version_info&amp;version_id='.$dr['id'].'" class="edit" title="Version Settings">'.SilkIcon('wrench.png').'</a>';
					echo '</td>';

					echo '<td width="50">';
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
		return 0;
	}
	echo '<table>';
	echo '<tr>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="240">Occupation/Program</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th>Tab Name</th>';
		echo '<th>Sort</th>';
		echo '<th width="180">Organization</th>';
		echo '<th width="285">Last Modified</th>';
	echo '</tr>';
    $count = 0;
	foreach( $connections as $c )
	{
        $count++;
		$d = $DB->SingleQuery('SELECT M.*, D.sidebar_text_right, CONCAT(U.first_name," ",U.last_name) AS modified_by, schools.school_name
			FROM post_drawing_main M
			JOIN post_drawings D ON D.parent_id=M.id
			LEFT JOIN users U ON M.last_modified_by=U.id
			LEFT JOIN schools ON M.school_id=schools.id
			WHERE M.id='.intval($c['post_id']).'
			ORDER BY name');

		echo '<tr>';
			echo '<td><a href="'.str_replace('%%', $d['id'], $links['delete']).'">' . SilkIcon('cross.png') . '</a></td>';
			echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $d['id'] . '">' . SilkIcon('cog.png') . '</a></td>';
			echo '<td>' . GetDrawingName($d['id'], 'post') . '</td>';
			echo '<td><a href="javascript:preview_drawing(\''.$d['code'].'\')" title="View Version">' . SilkIcon('magnifier.png') . '</a></td>';
			echo '<td>';
			if((int) $d['skillset_id'] < 1){
				echo '<a href="javascript:alert(\'This drawing is missing its skillset. Please edit it (by clicking the gear icon) and choose a skillset.\')" title="This drawing is missing its skillset.">' . SilkIcon('exclamation.png') . '</a>';
			} else {
				echo '&nbsp;';
			}
			echo '</td>';
			echo '<td><a href="javascript:get_embed_code_for_specific_tab(\''.$d['id'].'\', \''.$c['tab_name'].'\', \''.$type.'\')" title="Get Embed Code that opens to '.$c['tab_name'].'">' . SilkIcon('link.png') . '</a></td>';
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
		echo '<tr>';
		echo '<td colspan="2">&nbsp;</td>';
		echo '<td colspan="8"><i>' . $d['sidebar_text_right'] . '</i></td>';
		echo '</tr>';
	}
	echo '</table>';
	return $count;
}
