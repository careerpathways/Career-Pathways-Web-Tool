<?php


// first generate svg file

$str  = '';
$str .= '<?xml version="1.0" standalone="no" ?>'."\n";
$str .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
$str .= '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1000" height="800">'."\n";

$str .= '<defs>';
$str .= '<path id="arrowhead" d="M 0 0 L 8 9 L 0 18 L 0 0" stroke-width="0" />';
$str .= '</defs>';

$drawing = $DB->SingleQuery("
	SELECT *, drawings.last_modified
	FROM drawings, drawing_main
	WHERE drawing_main.id=drawings.parent_id
		AND drawings.id=".intval($_REQUEST['drawing_id']));
if( is_array($drawing) ) {
	$objects = $DB->MultiQuery("SELECT * FROM objects WHERE drawing_id=".intval($_REQUEST['drawing_id']));

	$str .= '<text x="'.(10).'" y="'.(50).'" ';
		$str .= 'fill="#'.$drawing['tagline_color_id'].'" font-weight="bold" font-size="40pt">Career Pathways</text>';

	$str .= '<text x="'.(10).'" y="'.(70).'" ';
		$str .= 'fill="#'.$drawing['tagline_color_id'].'" font-weight="bold" font-size="20pt">'.($drawing['name']).'</text>';

} else {
	$objects = array();
}

foreach( $objects as $obj ) {

	$content = unserialize($obj['content']);

	if( !array_key_exists('color',$content['config']) || $content['config']['color'] == 'undefined' ) {
		$content['config']['color'] = "333333";
	}

	switch( $content['type'] ) {
		case 'line':

			switch( $content['config']['direction'] ) {
				case 'v':
					$str .= '<line x1="'.$content['x'].'" y1="'.$content['y'].'" x2="'.($content['x']).'" y2="'.($content['y']+$content['h']).'" stroke="#'.$content['config']['color'].'" stroke-width="5"/>';
				break;
				case 'h':
					$str .= '<line x1="'.$content['x'].'" y1="'.$content['y'].'" x2="'.($content['x']+$content['w']).'" y2="'.($content['y']).'" stroke="#'.$content['config']['color'].'" stroke-width="5"/>';
				break;
			}

			break;
		case 'arrow':
			$trim = 6;
			$str .= '<g>';
				switch( $content['config']['direction'] ) {
					case 'e':
						$ar['x'] = $content['x']+$content['w']-12;
						$ar['y'] = $content['y']-9;
						$rot = "0";
						$str .= '<line x1="'.($content['x']).'" y1="'.$content['y'].'" ';
							$str .= 'x2="'.($content['x']+$content['w']-$trim).'" y2="'.$content['y'].'" ';
							$str .= 'stroke="#'.$content['config']['color'].'" stroke-width="5"/>';
						break;
					case 'w':
						$ar['x'] = $content['x']+12;
						$ar['y'] = $content['y']+9;
						$rot = "180 ".$ar['x'].' '.$ar['y'];
						$str .= '<line x1="'.($content['x']+$trim).'" y1="'.$content['y'].'" ';
							$str .= 'x2="'.($content['x']+$content['w']).'" y2="'.$content['y'].'" ';
							$str .= 'stroke="#'.$content['config']['color'].'" stroke-width="5"/>';
						break;
					case 'n':
						$ar['x'] = $content['x']-9;
						$ar['y'] = $content['y']+12;
						$rot = "-90 ".$ar['x'].' '.$ar['y'];
						$str .= '<line x1="'.$content['x'].'" y1="'.($content['y']+$trim).'" ';
							$str .= 'x2="'.$content['x'].'" y2="'.($content['y']+$content['h']).'" ';
							$str .= 'stroke="#'.$content['config']['color'].'" stroke-width="5"/>';
						break;
					case 's':
						$ar['x'] = $content['x']+9;
						$ar['y'] = $content['y']+$content['h']-12;
						$rot = "90 ".$ar['x'].' '.$ar['y'];
						$str .= '<line x1="'.$content['x'].'" y1="'.($content['y']).'" ';
							$str .= 'x2="'.$content['x'].'" y2="'.($content['y']+$content['h']-$trim).'" ';
							$str .= 'stroke="#'.$content['config']['color'].'" stroke-width="5"/>';
						break;
				}
				$str .= '<use x="'.$ar['x'].'" y="'.$ar['y'].'" fill="#'.$content['config']['color'].'" transform="rotate('.$rot.')" xlink:href="#arrowhead" />';
			$str .= '</g>';
			break;
		case 'box':
			$str .= '<g>';

				$str .= '<rect x="'.$content['x'].'" y="'.$content['y'].'" ';
					$str .= 'width="'.$content['w'].'" height="'.$content['h'].'" ';
					$str .= 'fill="#'.$content['config']['color'].'" rx="10" ry="10" />';

				$border = 5;
				$titleh = 20;
				$str .= '<rect x="'.($content['x']+$border).'" y="'.($content['y']+$titleh).'" ';
					$str .= 'width="'.($content['w']-($border*2)).'" height="'.($content['h']-$titleh-$border-10).'" ';
					$str .= 'fill="#FFFFFF" />';

				$str .= '<rect x="'.($content['x']+$border).'" y="'.($content['y']+$titleh+30).'" ';
					$str .= 'width="'.($content['w']-($border*2)).'" height="'.($content['h']-$titleh-$border-30).'" ';
					$str .= 'fill="#FFFFFF" rx="6" ry="6" />';

				$str .= '<text x="'.($content['x']+$border+$border).'" y="'.($content['y']+$titleh-$border).'" ';
					$str .= 'fill="white" font-weight="bold" font-size="8pt">'.strtoupper($content['config']['title']).'</text>';

				$lines = explode("\n",wordwrap(htmlspecialchars(strip_tags($content['config']['content'])),28,"\n"));
				foreach( $lines as $i=>$line ) {
					$str .= '<text x="'.($content['x']+$border+$border).'" ';
						$str .= 'y="'.($content['y']+$titleh+12+($i*12)).'" ';
						$str .= 'fill="black" font-size="8pt">'.($line).'</text>';
				}

			$str .= '</g>';
			break;
	}
	$str .= "\n";
}

$str .= '</svg>';

if( is_array($drawing) ) {

	$filename = $_REQUEST['drawing_id'].'.'.md5($_REQUEST['drawing_id'].".".$drawing['last_modified']);

	if( !file_exists('../cache/charts/'.$filename.'.svg') ) {
		$fp = fopen("../cache/charts/".$filename.".svg", "w");
		fwrite($fp, $str);
		fclose($fp);
	}

	// convert svg file to gif

	if( !file_exists('../cache/charts/'.$filename.'.gif') ) {
		echo shell_exec('/usr/bin/convert -resize 140x100! ../cache/charts/'.$filename.'.svg ../cache/charts/'.$filename.'.gif');
	}

	if( !file_exists('../cache/charts/'.$filename.'_full.gif') ) {
		echo shell_exec('/usr/bin/convert --font helvetica ../cache/charts/'.$filename.'.svg ../cache/charts/'.$filename.'_full.gif');
	}

}

?>
