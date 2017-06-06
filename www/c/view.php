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
    var cont = "<?=(Request('container')?Request('container'):'pathwaysContainer')?>";
    var pc = document.getElementById(cont);

    // If a height is specified on the embed container, resize the iFrame to
    // that height, not the full height of the drawing.
    // If a height is not defined, expand the container and iFrame to the
    // drawing's full height.
    var expandHeight;

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
    }

    if(pc.hasAttribute('height')) {
      expandHeight = pc.getAttribute('height');
    } else if(pc.style.height) {
      expandHeight = pc.style.height;
    }

    // Adjust height of container element and iFrame element itself, based on height of document within the iFrame.
    // Listen for a cross-origin message from the document within the iFrame.
    // That message should be posted to this window via the "parent" object.
    window.addEventListener("message", function(e) {
      if(undefined !== expandHeight) {
        // Set pathways container iFrame element height based on the iFrame's document's height
        fr.setAttribute("height", expandHeight);
      } else {
        try {
          if (e.data && e.data.messageId === 'drawingDocumentLoaded') {
            // Remove "http:" or "https://" for looser comparison since the embed script could be changed.
            if(e.origin.replace(/^(http:|https:)/g, "") == "<?=getBaseUrl()?>".replace(/^(http:|https:)/g, "")){
              // We get drawingHeight as a value from postMessage() (in the page that was loaded into the iFrame)
              // Set pathways container height based on the iFrame's document's height
              pc.setAttribute("height", e.data.drawingHeight + 'px');

              // Set pathways container iFrame element height based on the iFrame's document's height
              fr.setAttribute("height", e.data.drawingHeight + 'px');
            }
          }
        } catch(e) {
          // send log?
        }
      }
    }, false);

    document.getElementById('pathwaysContainer').appendChild(fr);
<?php
	}
	else {
		require('view/html.php');
	}
}
