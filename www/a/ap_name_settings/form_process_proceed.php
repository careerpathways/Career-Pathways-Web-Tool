<?php // Common area between POST and Roadmap APN upload. Prompt user to proceed? and show report. ?>

<?php if ($isDryrun): ?>
    <h3>Proceed?</h3>
    <a href="?dryrun=false&submitted=true&file=<?= urlencode($file) ?>">Yes</a>

    <br />
    <br />

    <h3>Exceptions</h3>
    <?= implode(', ', $apn_exceptions); ?>

    <br />
    <br />

    <h3>New Programs</h3>
    <div>
        <em><?= count($report['new_programs']) ?> to be imported.</em>
    </div>

    <?php if(count($report['new_programs']) > 0): ?>
        <?php foreach($report['new_programs'] as $np): ?>
            <div>
                <?= $np['approved_program_name'] ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>
            No new (unique) program names found.
        </div>
    <?php endif;?>

    <br />
    <br />

    <h3>Skipping</h3>
    <div>
        <em><?= count($report['skipped']) ?> skipped because they'd be duplicates.</em>
    </div>
    <br />
    <?php if(count($report['skipped']) > 0): ?>
        <?php foreach($report['skipped'] as $np): ?>
            <div>
                <?= $np['approved_program_name'] ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>
            Not skipping any programs.
        </div>
    <?php endif;?>

<?php else: ?>

    <h3>File Uploaded Successfully!</h3>
    <p>Number of new Approved Program Names: <?php echo count($report['new_programs']); ?></p>
    <p>Number skipped because they already exist: <?php echo count($report['skipped']); ?></p>
    <p><a href="/">Return to home page &gt;&gt;</a></p>

<?php endif; ?>
