
function ajaxFunction() {
  var xmlHttp;
  try {
    // Firefox, Opera 8.0+, Safari
    xmlHttp=new XMLHttpRequest();
  } catch (e) {
    // Internet Explorer
    try {
      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e) {
        alert("Your browser does not support AJAX!");
        return false;
      }
    }
  }
  return xmlHttp;
}


function doSomething(div_layer,url) {
	var xml = ajaxFunction();

	xml.onreadystatechange=function() {
		if(xml.readyState==4) {
			div_layer.innerHTML = xml.responseText;
		}
	}

    xml.open("GET",url,true);
    xml.send(null);
}

function getValue(obj, url) {
	var xml = ajaxFunction();

	xml.onreadystatechange=function() {
		if(xml.readyState==4) {
			obj.value = xml.responseText;
		}
	}

    xml.open("GET",url,true);
    xml.send(null);
}

function ajaxCallback(cbfunc, url) {
	var xml = ajaxFunction();

	xml.onreadystatechange=function() {
		if(xml.readyState==4) {
			cbfunc(xml.responseText);
		}
	}

    xml.open("GET",url,true);
    xml.send(null);
}

function doNoOutput(url) {
	var xml = ajaxFunction();

	xml.onreadystatechange=function() {
		if(xml.readyState==4) {

		}
	}

    xml.open("GET",url,true);
    xml.send(null);
}



  
function n() { }


function copyPopup(mode, version_id) {
	chGreybox.create('<div id="copyPopup"><iframe src="/a/copy_popup.php?mode='+mode+'&version_id='+version_id+'" style="width:400px;height:300px;"></iframe></div>', 400, 300, null, 'Copy This Version');
}

function publishPopup(mode, version_id) {
  var url;
  if( mode == "post" ) {
	url = "/a/post_drawings.php?action=publish_form&version_id=" + version_id;
  } else {
	url = "/a/drawings.php?action=publish_form&version_id=" + version_id;
  }
  ajaxCallback(function(data) {
    chGreybox.create(data, 400, 300, null, "Publish this Version");
  }, url);
}

function publishViewPopup(id, published, r) {
        var url = "/a/post_assurance_ajax.php?action=publish_form&view_id="+id+"&r="+r;
        var title = "Publish this View";
        if(published){
                title = "Unpublish this View";
        }
        ajaxCallback(function(data) {
                chGreybox.create(data, 400, 300, null, title);
        }, url);
}

function viewAssurancePopup(id, r) {
        var url = "/a/post_assurance_ajax.php?action=assurance_form&view_id="+id+"&r="+r;
        var title = "Add New Assurance Agreement";
        ajaxCallback(function(data) {
                chGreybox.create(data, 400, 300, null, title);
        }, url);
}