<?php
if(isset($response)){
	header('Content-type: application/json');
	echo json_encode($response);
} else {
	header('HTTP/1.0 404 Not Found');
    echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head>
        <title>404 Not Found</title>
        </head><body>
        <h1>404 - Not Found</h1><p>Page not found. No response object provided.</p><hr>
        </body></html>';
    die();
}
