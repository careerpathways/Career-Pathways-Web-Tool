<?php
chdir("..");
include("inc.php");

$_REQUEST['d'] = CleanDrawingCode($_REQUEST['d']);

if( KeyInRequest('version_id') ) {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.name, school_id, published, frozen, sk.title AS skillset, program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE drawing_main.id='".$DB->Safe($_REQUEST['id'])."'
			AND drawings.id=".intval($_REQUEST['version_id']));

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', $_REQUEST['id'], $_REQUEST['version_id']);
	}

} else if( KeyInRequest('v') ) {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.name, school_id, published, frozen, sk.title AS skillset, program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE code='".$DB->Safe($_REQUEST['d'])."'
			AND drawings.version_num=".intval($_REQUEST['v']));

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', 0, 0, $_REQUEST['d'], $_REQUEST['v']);
	}

} else if (KeyInRequest('id')) {
	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.name, school_id, published, frozen, sk.title AS skillset, program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE drawing_main.id='".$DB->Safe($_REQUEST['id'])."'
		AND published=1");

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', $_REQUEST['id']);
	}

} else {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.name, school_id, published, frozen, sk.title AS skillset, program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE code='".$DB->Safe($_REQUEST['d'])."'
		AND published=1");

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', 0, 0, $_REQUEST['d']);
	}

}

if( $drawing['program_id'] == 0 )
	$drawing_name = $drawing['name'];
else
{
	$program = $DB->SingleQuery('SELECT * FROM programs WHERE id = '.$drawing['program_id']);
	$drawing_name = $program['title'];
}

// determine the format based on the request parameter
if (isset($_REQUEST['format'])) {
	$format = $_REQUEST['format'];
}
else {
	$format = 'html';
}

if( $_REQUEST['page'] == 'text' ) {
	$_REQUEST['xml'] = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/'.$_REQUEST['id'].'/data.xml';
	require('view/text.php');
} else {
	if ($format === 'xml') {
		$_REQUEST['id'] = $drawing['id'];
		require('view/xml.php');
	}
	else if ($format === 'js') {
		header("Content-type: text/javascript");
?>
		document.write('<script src="http://<?=$_SERVER['SERVER_NAME']?>/c/log/pathways/<?=$_REQUEST['id']?>?url='+encodeURIComponent(window.location.href)+'"></script>');

		var pc = document.getElementById("<?=(Request('container')?Request('container'):'pathwaysContainer')?>");

        //from MS site on how to detect IE versions
        var rv = -1; // Return value assumes failure.
        if (navigator.appName == 'Microsoft Internet Explorer')
        {
        var ua = navigator.userAgent;
        var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
        rv = parseFloat( RegExp.$1 );
        }

        if( rv < 9.0 && typeof VBArray != "undefined" ) {  //all IE < 9
			var fr = document.createElement('<iframe src="http://<?=$_SERVER['SERVER_NAME']?>/c/published/<?=$_REQUEST['id']?>/embed.html" width="'+pc.style.width+'" height="'+pc.style.height+'" frameborder="0" scrolling="auto"></iframe>');
		} else {
			var fr = document.createElement('iframe');
			fr.setAttribute("width", pc.style.width);
			fr.setAttribute("height", pc.style.height);
			fr.setAttribute("src", "http://<?=$_SERVER['SERVER_NAME']?>/c/published/<?=$_REQUEST['id']?>/embed.html");
			fr.setAttribute("frameborder", 0);
			fr.setAttribute("scrolling", "auto");
		}

		document.getElementById('pathwaysContainer').appendChild(fr);
<?php
	}
	else {
		require('view/html.php');
	}
}

?>