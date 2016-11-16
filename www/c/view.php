<?php
chdir("..");
include("inc.php");

$_REQUEST['d'] = CleanDrawingCode($_REQUEST['d']);

if( KeyInRequest('version_id') ) {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.id as parent_id,
            drawing_main.last_modified,
            drawing_main.show_updated,
            drawing_main.show_pdf_ada_links,
            drawing_main.name,
            school_id,
            published,
            frozen,
            sk.title AS skillset, program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE drawing_main.id='".$DB->Safe($_REQUEST['id'])."'
			AND drawings.id=".intval($_REQUEST['version_id'])."
                        AND drawings.deleted=0");

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', $_REQUEST['id'], $_REQUEST['version_id']);
	}

} else if( KeyInRequest('v') ) {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.id as parent_id,
            drawing_main.last_modified,
            drawing_main.show_updated,
            drawing_main.show_pdf_ada_links,
            drawing_main.name,
            school_id,
            published,
            frozen,
            sk.title AS skillset,
            program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE code='".$DB->Safe($_REQUEST['d'])."'
			AND drawings.version_num=".intval($_REQUEST['v'])."
                        AND drawings.deleted=0");

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', 0, 0, $_REQUEST['d'], $_REQUEST['v']);
	}

} else if (KeyInRequest('id')) {
	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.id as parent_id,
            drawing_main.last_modified,
            drawing_main.show_updated,
            drawing_main.show_pdf_ada_links,
            drawing_main.name,
            school_id,
            published,
            frozen,
            sk.title AS skillset, program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE drawing_main.id='".$DB->Safe($_REQUEST['id'])."'
		AND published=1
                AND drawings.deleted=0");

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', $_REQUEST['id']);
	}
} else {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.id as parent_id, drawing_main.last_modified, drawing_main.show_updated, drawing_main.name, school_id, published, frozen, sk.title AS skillset, program_id
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE code='".$DB->Safe($_REQUEST['d'])."'
		AND published=1
                AND drawings.deleted=0");

	if( !is_array($drawing) ) {
		drawing_not_found('roadmap', 0, 0, $_REQUEST['d']);
	}

}

$drawing_name =  GetDrawingName($_REQUEST['id'], 'roadmap');

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
		document.write('<script src="<?=getBaseUrl()?>/c/log/pathways/<?=$_REQUEST['id']?>?url='+encodeURIComponent(window.location.href)+'"></script>');

		var pc = document.getElementById("<?=(Request('container')?Request('container'):'pathwaysContainer')?>");

        /**
		 * detect IE
		 * returns version of IE or false, if browser is not Internet Explorer
		 */
		function IEversion() {
		    var ua = window.navigator.userAgent;

		    var msie = ua.indexOf('MSIE ');
		    if (msie > 0) {
		        // IE 10 or older => return version number
		        return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
		    }

		    var trident = ua.indexOf('Trident/');
		    if (trident > 0) {
		        // IE 11 => return version number
		        var rv = ua.indexOf('rv:');
		        return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
		    }

		    var edge = ua.indexOf('Edge/');
		    if (edge > 0) {
		       // IE 12 => return version number
		       return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
		    }

		    // other browser
		    return false;
		}

        if( IEversion() && IEversion() < 9.0 && typeof VBArray != "undefined" ) {  //all IE < 9
			var fr = document.createElement('<iframe src="<?=getBaseUrl()?>/c/published/<?=$_REQUEST['id']?>/embed.html" width="'+pc.style.width+'" height="'+pc.style.height+'" frameborder="0" scrolling="auto"></iframe>');
		} else {
			var fr = document.createElement('iframe');
			fr.setAttribute("width", pc.style.width);
			fr.setAttribute("src", "<?=getBaseUrl()?>/c/published/<?=$_REQUEST['id']?>/embed.html");
			fr.setAttribute("frameborder", 0);
            fr.setAttribute("scrolling", "auto");
            fr.setAttribute("id", "idIframe");
            fr.setAttribute("onload", "iframeLoaded()");
		}

        function iframeLoaded() {
            var iFrameID = document.getElementById('idIframe');
            if(iFrameID) {
                var contentHeight = iFrameID.contentWindow.document.body.scrollHeight; //add a small amount to compensate for scrollbar
                document.getElementById('pathwaysContainer').setAttribute("height", contentHeight);
                iFrameID.height = "";
                iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight;

            }   
        }
		document.getElementById('pathwaysContainer').appendChild(fr);
<?php
	}
	else {
		require('view/html.php');
	}
}



