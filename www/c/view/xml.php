<?php
header("Content-type: text/xml");
$drawing = $DB->SingleQuery("
			SELECT drawings.*, drawing_main.name, schools.school_name, schools.school_abbr
			FROM drawings, drawing_main, schools
			WHERE drawing_main.id=drawings.parent_id
				AND school_id=schools.id
				AND drawings.id=".intval($_REQUEST['id']));
$objects = $DB->MultiQuery("SELECT * FROM objects WHERE drawing_id=".$drawing['id']);
?>
<drawing id="<?= $drawing['parent_id'] ?>">
	<name><?= htmlspecialchars($drawing['name']) ?></name>
	<schoolName><?= htmlspecialchars($drawing['school_name']) ?></schoolName>
	<schoolAbbr><?= htmlspecialchars($drawing['school_abbr']) ?></schoolAbbr>
	<version id="<?= $drawing['id'] ?>" number="<?= $drawing['version_num'] ?>" published="<?= $drawing['published'] ? 'true' : 'false' ?>">
	<?php foreach ($objects as $object) :
		$content = unserialize($object['content']);
		$program = $DB->SingleQuery("SELECT * FROM object_type JOIN types ON (object_type.type_id = types.id) WHERE object_type.object_id='" . $object['id'] . "' AND types.family ='program'");
		if( !is_array($program) ) $program = array();
	?>
		<<?= $content['type'] ?> id="<?= $object['id'] ?>" <?php if (array_key_exists('type_id', $program)) : ?> programId="<?= $program['type_id'] ?>" <?php endif; ?>>
		  	<?php if (array_key_exists('config', $content)) : ?>
			<?php if (array_key_exists('title', $content['config'])) : ?><title><?= htmlspecialchars($content['config']['title']) ?></title><?php endif; ?>
			<?php if (array_key_exists('content', $content['config'])) : ?><content><?= htmlspecialchars($content['config']['content']) ?></content><?php endif; ?>
			<?php endif; ?>
			<?php
				$connections = $DB->MultiQuery("SELECT * FROM connections WHERE source_object_id=".$object['id']);
				foreach ($connections as $connection) : ?>
			<connection id="<?= $connection['id'] ?>" destinationId="<?= $connection['destination_object_id'] ?>"/>
			<?php endforeach; ?>
		</<?= $content['type'] ?>>
	<? endforeach; ?>

	</version>
</drawing>