<?php
	require_once('inc.php');
	//logmsg( "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX\n" );
	//logmsg( "GET ids: ".$_GET['ids']."\n" );

	$checked = FALSE;
	if(substr($_GET['ids'], 0, 1) == 'c')
	{
		$checked = TRUE;
		$_GET['ids'] = substr($_GET['ids'], 1);
	}

	$largeMode = FALSE;
	if(substr($_GET['ids'], 0, 1) == 'b')
	{
		$largeMode = TRUE;
		$_GET['ids'] = substr($_GET['ids'], 1);
	}

	$ids = explode('-', $_GET['ids']);
	asort($ids);

	if(!is_array($ids) || count($ids) <= 0)
		exit();

	header('Content-Type: image/png');

	$folder = $SITE->cache_path("legend");

	$finalPath = $folder . "/" . $_GET['ids'] . '.png';

	//logmsg( "FinalPath: $finalPath\n" );
	//logmsg( "checked: $checked\n" );
	//logmsg( "largeMode: $largeMode\n\n" );

	//JGD Don't used cached image. Something is causing it to use the cached (checked) image on new POST drawings. Only happens every
	//JGD once and a while and we're on a fast enuf server now that I don't care to figure it out right now.
	//if(file_exists($finalPath) && !$checked && !$largeMode)
	//{
	//	logmsg( "Using cached image." );
	//	die(file_get_contents($finalPath));
	//}

	// Gather the list of graphics requeste by the URL
	$sql = "SELECT `graphic` FROM `post_legend` WHERE";
	foreach($ids as $id)
		$sql .= " `id` = '" . $id . "' OR";
	$sql = substr($sql, 0, -3) . " ORDER BY `id` ASC";
	$icons = $DB->MultiQuery($sql);

	if($checked)
	{
		$width = $height = 20;
		$img = initializeImage($width, $height);

		$copy = imagecreatefrompng($icons[0]['graphic']);
		imagecopy($img, $copy, 4, 4, 0, 0, 12, 12);
		$copy = imagecreatefrompng('circle.png');
		imagecopy($img, $copy, 0, 0, 0, 0, 20, 20);

		// Output the generated image
		imagesavealpha($img, TRUE);
		imagepng($img);
		die();
	}
	elseif($largeMode)
	{
		$width = $height = 20;
		$img = initializeImage($width, $height);
		$copy = imagecreatefrompng($icons[0]['graphic']);
		imagecopy($img, $copy, 4, 4, 0, 0, 12, 12);
		// Output the generated image
		imagesavealpha($img, TRUE);
		imagepng($img);
		die();
	}

	// Precalculate our width and height
	$width = (12 * count($ids)) + (2 * (count($ids) - 1));
	$height = 12;
	$img = initializeImage($width, $height);

	$x = 0;
	foreach($icons as $icon)
	{
		// Copy in the graphic to the final image
		$copy = imagecreatefrompng($icon['graphic']);
		imagecopy($img, $copy, $x, 0, 0, 0, 12, 12);
		$x += 14;
	}//foreach

	imagesavealpha($img, TRUE);
	imagepng($img, $finalPath);
	die(file_get_contents($finalPath));

	function initializeImage($width, $height)
	{
		// Create the final image
		$img = imagecreatetruecolor($width, $height);
		imagealphablending($img, FALSE);
		$blank = imagecolorallocatealpha($img, 255, 255, 255, 127);
		imagefilledrectangle($img, 0, 0, $width, $height, $blank);
		imagealphablending($img, TRUE);
		return $img;
	}
?>
