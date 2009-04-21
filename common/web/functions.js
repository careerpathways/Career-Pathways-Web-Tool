                function swapPairs(s){
                  var res = "";
                  for (var i=0; i<s.length; i++){
                        var ch = s.charCodeAt(i) ;
                        res += String.fromCharCode(
                                   ( ch & 0xF0 ) +
                                   ((ch & 0x0C)>>2) +
                                   ((ch & 0x03)<<2)
                                   );
                        }
                  return res;
                }


function getLayer(whichLayer) {
	if (document.getElementById) {
		// this is the way the standards work
		var layer = document.getElementById(whichLayer);
	} else if (document.all) {
		// this is the way old msie versions work
		var layer = document.all[whichLayer];
	} else if (document.layers) {
		// this is the way nn4 works
		var layer = document.layers[whichLayer];
	}
	return layer;
}

function toggleLayer(whichLayer) {
	if (document.getElementById) {
		// this is the way the standards work
		var style2 = document.getElementById(whichLayer).style;
		style2.display = style2.display=="block"?"none":"block";
	} else if (document.all) {
		// this is the way old msie versions work
		var style2 = document.all[whichLayer].style;
		style2.display = style2.display=="block"?"none":"block";
	} else if (document.layers) {
		// this is the way nn4 works
		var style2 = document.layers[whichLayer].style;
		style2.display = style2.display=="block"?"none":"block";
	}
}

function hideLayer(whichLayer) {
	var style2 = document.getElementById(whichLayer).style;
	style2.display = "none";
}
function showLayer(whichLayer) {
	var style2 = document.getElementById(whichLayer).style;
	style2.display = "block";
}
