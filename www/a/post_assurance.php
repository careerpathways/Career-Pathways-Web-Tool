<?php
    chdir("..");
    include("inc.php");

    $MODE = 'post_assurance';
    ModuleInit('post_assurance');

    PrintHeader();

    function getSignatureData($viewId)
    {
        global $DB;

        $postSql = "SELECT `PostView`.`name`, `PostView`.`code`, `School`.`school_name` FROM vpost_views AS `PostView` INNER JOIN schools AS `School` ON `School`.`id` = `PostView`.`school_id` WHERE `PostView`.`id` = '$viewId'";
        $rawData = $DB->SingleQuery($postSql);

//        print "<p>SQL</p><pre>" . print_r($postSql, true) . "</pre>";
//        print "<p>Data</p><pre>" . print_r($rawData, true) . "</pre>";

        $data['school_name'] = $rawData['school_name'];
        $data['post_name']   = $rawData['name'];
        $data['post_code']   = $rawData['code'];

        return $data;
    }

    function getReportData()
    {
        global $DB;

        $isStateAdmin = $_SESSION['user_level'] == CPUSER_STATEADMIN;
        $reportSQL = "SELECT `VPostView`.`id` as `PostId`, `VPostView`.`school_id`, `School`.`school_name`, `VPostView`.`name` AS `PostName`, `VPostView`.`code` AS `PostCode`, `Category`.`name`, `Signature`.`id` AS `SigId`"
            . " FROM (vpost_views AS `VPostView`, signature_categories AS `Category`)"
            . " LEFT JOIN `signatures` AS `Signature`"
            . " ON `Signature`.`vpost_view_id`=`VPostView`.`id` AND `Signature`.`category_id`=`Category`.`id`"
            . " LEFT JOIN `schools` AS `School`"
            . " ON `School`.`id` = `VPostView`.`school_id`"
            . " WHERE `VPostView`.`name` IS NOT NULL AND `VPostView`.`name` != ''";
        if (!$isStateAdmin) {
            if (!isset($_SESSION['school_id'])) {
                throw new Exception( 'Must have school ID set if user is not state administrator.' );
            }
            $userSchoolId = $_SESSION['school_id'];
            $reportSQL .= " AND `VPostView`.`school_id`='$userSchoolId'";
        }
//        $reportSQL .= " ORDER BY `VPostView`.`school_id`, `VPostView`.`last_modified` DESC, `Category`.`id`";
        $reportSQL .= " ORDER BY `School`.`school_name`, `VPostView`.`name`, `VPostView`.`last_modified` DESC, `Category`.`id`";
//        print "<pre> $reportSQL </pre>";
        $rawData = $DB->MultiQuery( $reportSQL );
//        print "<pre> " . print_r( $rawData, true ) . "</pre>";
        if (!$rawData || count($rawData) == 0) return array();

        $reportData = array();
        foreach ($rawData as $row) {
            $dataRow = $row;
            $schoolId = $row['school_id'];
            $schoolName = $row['school_name'];
            $postId = $row['PostId'];

            if ($schoolName) {
                $reportData[$schoolId]['school_name'] = $schoolName;
            } else {
                $reportData[$schoolId]['school_name'] = '<Deleted School>';
            }

            $reportData[$schoolId]['VPostView'][$postId]['PostName'] = $row['PostName'];
            $reportData[$schoolId]['VPostView'][$postId]['PostCode'] = $row['PostCode'];

            $sigDataKey = ($row['SigId']>0) ? 'completed' : 'pending';
            $reportData[$schoolId]['VPostView'][$postId][$sigDataKey][] = $row['name'];
        }
//        print "<pre> " . print_r( $reportData, true ) . "</pre>";
        return $reportData;
    }

    if (Request('id')) {
        $viewId = $_REQUEST['id'];
    } else {
        $viewId = false;
    }

    $userId = $_SESSION['user_id'];
?>
<?php if ($viewId): ?>
<table>
    <tr>
        <td valign="top">
            <div style="float: left;">
                <h2>Steps for Identifying a Program of Study</h2>

                <h3>(in preparation for approval)</h3>
                <ol>
                    <li>When a Cluster, Pathway, and Field or Program of Study has been identified, the
                        groundwork is there for a Program of Study to be developed.
                    </li>
                    <li>If you are a high school or district wishing to have an Approved Program of Study,
                        then you can begin completing a/the template.
                    </li>
                    <li>Select the appropriate template, based on one of the 16 Career Clusters.</li>
                    <li>Fill in the required core courses for graduation, as defined by your district or high
                        school in the appropriate year (9-12 grades).
                    </li>
                    <li>Fill in the concentration of CTE courses your district or high school offers that align
                        with the particular Program of Study.
                    </li>
                    <li>Fill in the related academic courses that are recommended to prepare a student for
                        entry into the postsecondary portion of the Program of Study.
                    </li>
                    <li>Make sure that at least the minimum criteria have been met, as outlined in the
                        attached Program of Study Assurances page.
                    </li>
                    <li>Highlight the courses where there is an articulation agreement or dual credit
                        agreement. This will help determine postsecondary alignment.
                    </li>
                    <li>If a student were to complete the courses from the high school portion of this
                        template, what programs at the postsecondary level would the student be prepared to
                        enter, without remediation? This will provide information for the postsecondary
                        portion of the template. (See examples. You will note that the postsecondary portion
                        does not need to be as specific as the secondary portion of the template.)
                    </li>
                    <li>Identify those areas of postsecondary study, along with the college where the program
                        can be found (http://www.sbctc.ctc.edu/college/_e-wkforceproftechprograms.aspx).
                    </li>
                    <li>Determine who your local Tech Prep Director is. Notify him/her that you have a
                        program of study that needs to be moved forward for approval. (See
                        http://www.sbctc.ctc.edu/college/_e-wkforcetechprep.aspx for a list of consortium
                        directors.) If the Tech Prep director finds that the Program is not offered at one of the
                        consortium’s colleges, then the director will locate a nearby college where the
                        program is offered, and will contact the Tech Prep director for that college to
                        facilitate the approval.
                    </li>
                    <li>If there is a possible dual credit opportunity or articulation agreement, the Tech Prep
                        director will work with you or will refer you to the appropriate director to facilitate
                        the agreements.
                    </li>
                    <li>Once this template is complete the Program of Study Assurances should be signed by
                        the secondary CTE director, the postsecondary institution’s Workforce Dean, and the
                        appropriate Tech Prep Director(s).
                    </li>
                    <li>The completed and signed Assurances form will be held on file by the Tech Prep
                        director and copies sent to the secondary CTE director and workforce dean. Programs
                        of Study on file will be included in the secondary and postsecondary annual Perkins
                        plan.
                    </li>
                </ol>
                <h2>Program of Study Assurances</h2>

                <h3>Minimum Criteria</h3>
                <ul>
                    <li>The secondary CTE, academic, and appropriate elective courses are included, as
                        well as the state and local graduation requirements.
                    </li>
                    <li>The secondary Program of Study includes leadership standards.</li>
                    <li>The secondary Program of Study includes employability standards, where
                        appropriate.
                    </li>
                    <li>The Program of Study includes coherent and rigorous coursework in a non-
                        duplicative sequence of courses from secondary to postsecondary.
                    </li>
                    <li>
                        Completion of the secondary Program of Study prepares students for entry into
                        the postsecondary program or apprenticeship.
                    </li>
                    <li>
                        Program of Study courses include appropriate state standards and/or industry
                        skills standards.
                    </li>
                    <li>
                        Program of Study leads to an industry recognized credential; academic certificate
                        or degree; or employment.
                    </li>
                    <li>
                </ul>
                <h3>Exceeds Minimum Criteria</h3>
                <ul>
                    <li>There is a dual credit articulation agreement on file for one or more courses in the
                        secondary/postsecondary Program of Study.
                    </li>
                    <li>
                        The Program of Study includes multiple entry and/or exit points at the post-
                        secondary level.
                    </li>
                    <li>
                        The Program of Study offers course work and skill development for self-
                        employment and/or entrepreneurial opportunities.
                    </li>
                    <li>
                        The Program of Study is linked to a comprehensive school counseling program,
                        such as Navigation 101.
                    </li>
                    <li>
                        There is program alignment between the community and technical college
                        Program of Study and a baccalaureate program, with a signed articulation
                        agreement on file.
                    </li>
                    <li>
                        The Program of Study is linked to a skill panel or a Center of Excellence.
                    </li>
                </ul>
            </div>
        </td>
        <td width="300px" valign="top">
            <div>
                <?php $data = getSignatureData($viewId); ?><a href="/a/post_views.php?id=<?= $viewId ?>">
                <h1><?= $data['school_name'] ?></h1>

                <h2><?= $data['post_name'] ?> (<?= $data['post_code'] ?>)</h2></a>
                <?php if ($viewId): ?>
                <?php
                $sigPermissionsQuery  = "SELECT category_id FROM signature_categories_users WHERE user_id = '$userId'";
                $sigPermissionsResult = $DB->MultiQuery($sigPermissionsQuery);
                $sigPermissions       = array();
                foreach ($sigPermissionsResult as $result) {
                    $sigPermissions[$result['category_id']] = true;
                }

                $viewsSigsQuery = "SELECT `SignatureCategory`.`id`, `SignatureCategory`.name, `User`.`email`, CONCAT(`User`.`first_name`, ' ', `User`.`last_name`) AS `username`, `Signature`.`date_signed`" . " FROM `signature_categories` AS `SignatureCategory`" . " LEFT JOIN `signatures` AS `Signature` ON `SignatureCategory`.`id` = `Signature`.`category_id`" . " AND `Signature`.`vpost_view_id` = '" . $viewId . "'" . " LEFT JOIN `users` AS `User` ON `Signature`.`user_id` = `User`.`id`";
                $signatures     = $DB->MultiQuery($viewsSigsQuery);

                // If we need to group signatures, this is where we do it.
                $categories = array();
                foreach ($signatures as $signature) {
                    $categories[$signature['id']]['name'] = $signature['name'];
                    $categories[$signature['id']]['sigs'] = array();
                    if ($signature['email']) {
                        $sig['date_signed']                     = $signature['date_signed'];
                        $sig['email']                           = $signature['email'];
                        $sig['name']                            = $signature['username'];
                        $categories[$signature['id']]['sigs'][] = $sig;
                    }
                }
                ?>
                <?php foreach ($categories as $catId => $category): ?>
                    <?php $sigCount = count($category['sigs']); ?>
                    <p>
                        <?php if ($sigCount > 0): ?>
                        <input type="checkbox" enabled="0" checked="checked" disabled="disabled"/>
                        <?php else: ?>
                        <input type="checkbox" enabled="0" disabled="disabled"/>
                        <?php endif; ?>
                        <?= $category['name'] ?>:
                        <ul>
                        <?php if (!$sigCount && isset($sigPermissions[$catId])): ?>
                            <?php $signViewLinkUrl = '/a/post_views?assurance=1&action=sign&id=' . $viewId . '&category_id=' . $catId; ?>
                            <a href="<?= $signViewLinkUrl ?>"><img src="/common/silk/script_edit.png" /> Sign as this role.</a>
                        <?php endif; ?>
                    </p>
                    <?php if ($sigCount > 0): ?>
                        <?php foreach ($category['sigs'] as $sig): ?>
                            Signed by <?= $sig['name'] ?>
                            on <?=  date_format(new DateTime($sig['date_signed']), 'Y-m-d')?></li>
                            <?php endforeach; ?>
                    <?php else: ?>
                        No signatures on file.
                    <?php endif; ?>
                  </ul>
                    <?php endforeach; ?>
                <?php else: ?>&nbsp
                <?php endif;?>
            </div>
        </td>
    </tr>
</table>
<?php else: ?>
<?php
    $reportData = getReportData();
?>
<table>
    <?php foreach ($reportData as $schoolId => $schoolReport): ?>
        <tr class="postAssuranceReport schoolNameSpacer">
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr class="postAssuranceReport schoolName">
            <td colspan="6"> <h2><?= $schoolReport['school_name'] ?><!-- <span class="schoolId"> <nobr>(id: <?= $schoolId ?>)</nobr></span> --></h2></td>
        </tr>
    <?php $firstReport = true; ?>
        <?php foreach ($schoolReport['VPostView'] as $postId => $postReport): ?>
            <?php if ($firstReport): ?>
            <tr class="postAssuranceReport splitter"><td colspan="6"><hr/></td></tr>
            <tr class="postAssuranceReport postName">
                <td>&nbsp</td>
                <?php $assuranceUrl = "/a/post_assurance.php?id=" . $postId; ?>
                <td colspan="5">
                    <a href="<?php echo $assuranceUrl; ?>">
                        <?= $postReport['PostName'] ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th><!-- School --> &nbsp;</th>
                <th><!-- POST View --> &nbsp;</th>
                <th class="postAssuranceReport icon"> &nbsp; <img src="/common/silk/script_edit.png" /></th>
                <th class="postAssuranceReport">Completed Signatures</th>
                <th class="postAssuranceReport icon"> &nbsp; <img src="/common/silk/script_delete.png" /></th>
                <th class="postAssuranceReport">Pending Signatures</th>
            </tr>
            <tr class="postAssuranceReport data">
                <td><!-- <?= $schoolId ?> --> &nbsp; </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="postAssuranceReport completed"><?= (isset($postReport['completed'])) ? implode( "<br/>", $postReport['completed']) : "None" ?></td>
                <td>&nbsp;</td>
                <td class="postAssuranceReport pending"><?= (isset($postReport['pending'])) ? implode( "<br/>", $postReport['pending']) : "None" ?></td>
            </tr>
            <?php else: ?>
            <?php $firstReport = false; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php PrintFooter(); ?>