<?php
    chdir("..");
    //PDF call passes session ID via command line call. see /pdf/index.php
    if(isset($_GET['pdf_format']) && isset($_GET['session_id'])){
        session_id($_GET['session_id']);
    }
    include("inc.php");

    $MODE = 'post_assurance';
    ModuleInit('post_assurance');
    global $TEMPLATE;
    

    function ShowSymbolLegend() {
            //$helpFile = 'drawing_list';
            //$onlyLegend = TRUE;
            require('view_toolbar.php');
            //require('view/drawings/helpbar.php');
    }

    if(isset($_GET['pdf_format'])){?>
    	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html>
            <head>
            	<title><?= $page_title ?></title>
                <link rel="stylesheet" href="/styles.css" type="text/css"/>
            </head>
            <body>       
    <?php } else {
        array_push($TEMPLATE->addl_scripts,'https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js');
        array_push($TEMPLATE->addl_scripts,'/files/js/assurance.js');
        array_push($TEMPLATE->addl_scripts,'/files/greybox.js');
        $TEMPLATE->addl_scripts[] = '/common/URLfunctions1.js';
        $TEMPLATE->toolbar_function = 'ShowSymbolLegend';
    PrintHeader();

    }
    
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
        $reportSQL = "SELECT `VPostView`.`id` as `PostId`, `VPostView`.`school_id`, `School`.`school_name`, `VPostView`.`name` AS `PostName`, `VPostView`.`code` AS `PostCode`, `Category`.`description` as 'name', `Signature`.`id` AS `SigId`"
            . " FROM (vpost_views AS `VPostView`, requirements AS `Category`)"
            . " LEFT JOIN `assurances` on `VPostView`.`id` = `assurances`.`vpost_view_id`"
            . " LEFT JOIN `assurance_requirements_ct` AS `Signature`"
            . " ON `Signature`.`assurance_id`=`assurances`.`id` AND `Signature`.`requirement_id`=`Category`.`id`"
            . " JOIN `schools` AS `School`"
            . " ON `School`.`id` = `VPostView`.`school_id`"
            . " WHERE `VPostView`.`name` IS NOT NULL AND `VPostView`.`name` != ''"
            . " AND requirement_type = 'stakeholder'";
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
<?php 
if ($viewId): 
    if(isset($_GET['pdf_format'])){
        $view = $DB->singleQuery("SELECT published, DATE_FORMAT(assurances.created_date,'%m/%d/%Y') as 'created_date', DATE_FORMAT(assurances.last_signed_date,'%m/%d/%Y') as 'last_signed_date' FROM vpost_views JOIN assurances ON vpost_views.id=assurances.vpost_view_id WHERE vpost_views.id=".Request('id')." AND assurances.id=".Request('assurance_id'));
    }
    if(isset($view) && !$view['published'] && !isset($_SESSION['user_id'])){
?>
       <h1>This view assurance is unavailable.</h1>
<?php
    } else {
        if(isset($_GET['pdf_format'])){  
?> 
           <h2>Program of Study Assurance Agreement, Dated <?=$view['last_signed_date']?></h2>
        <?php }?>
<table>
    <tr>
        <td valign="top">
            <div style="float: left;">
                <h2>Steps for Identifying a Program of Study</h2>

                <h3 style="margin-top:0px;">In Preparation for Approval</h3>
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
                        can be found. <em>(Refer to <a href="http://www.sbctc.ctc.edu/college/_e-wkforceproftechprograms.aspx" target="_blank">SBCTC Professional-Technical Programs</a>.)</em>
                    </li>
                    <li>Determine who your local Tech Prep Director is. Notify him/her that you have a
                        program of study that needs to be moved forward for approval. <em>(Refer to
                        <a href="http://www.sbctc.ctc.edu/college/_e-wkforcetechprep.aspx" target="_blank">SBCTC Workforce Tech Prep</a> 
                        for a list of consortium directors.)</em> If the Tech Prep director finds that the Program is not offered at one of the
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

                <h3 style="margin-top:0px;">Minimum Criteria</h3>
                <ul class="category_list">
                    <?php 
                    $userQuery = "SELECT role_id FROM users_roles WHERE user_id=$userId and role_id=3;";
                    $results = $DB->MultiQuery($userQuery);
                    $is_director = false;
                    foreach($results as $row){
                        $is_director = true;
                        break;
                    }
                    $enabled = $is_director?'':' disabled="disabled" ';
                    $viewsSigsQuery = "SELECT requirements.id, ".
                                        "requirements.description," . 
                                        "assurances.id AS assurance_id, " .
                                        "IFNULL(assurance_requirements_ct.date_signed,FALSE) as 'date_signed' " . 
                                      "FROM requirements " . 
                                      "LEFT JOIN ( " . 
                                           " assurances 
                                                INNER JOIN assurance_requirements_ct ".
                                                      "ON assurances.id = assurance_requirements_ct.assurance_id ".
                                                      "AND ( assurances.vpost_view_id = '" . $viewId . "' OR  assurances.vpost_view_id IS NULL ) ".
                                                      (isset($_GET['pdf_format'])?"":" AND assurances.valid=TRUE ") .
                                                      (Request('assurance_id')? " AND assurances.id=".Request('assurance_id'):"") .
                                           " ) ON requirements.id = assurance_requirements_ct.requirement_id " .
                                      "WHERE " .  
                                        " requirements.requirement_type='minimum' " .
                                       	" ;";
                    //die($viewsSigsQuery);
                    $signatures     = $DB->MultiQuery($viewsSigsQuery);
                    foreach($signatures as $row){
                        $checked = (empty($row['assurance_id'])) ? "" :  ' checked="checked" ';
                        echo('<li><div class="checkbox_container"><input type="checkbox" value="'.$row['id'].'_'.$viewId.'"'.$checked.$enabled.'></div><div class="description_container">'.$row['description'].'</div><div style="clear:both;"></div></li>');

                    }?>
                </ul>

                <h3 style="margin-top:0px;">Exceeds Minimum Criteria</h3>
                <ul class="category_list">
                    <?php 
                    $viewsSigsQuery = "SELECT requirements.id, ".
                                        "requirements.description," . 
                                        "assurances.id AS assurance_id, " .
                                        "IFNULL(assurance_requirements_ct.date_signed,FALSE) as 'date_signed' " . 
                                      "FROM requirements " . 
                    					"LEFT JOIN ( " . 
                                           " assurances 
                                                INNER JOIN assurance_requirements_ct ".
                                                      "ON assurances.id = assurance_requirements_ct.assurance_id " .
                                                      "AND ( assurances.vpost_view_id = '" . $viewId . "' OR  assurances.vpost_view_id IS NULL ) ".
                                                      (isset($_GET['pdf_format'])?"":" AND assurances.valid=TRUE ") .
                                                      (Request('assurance_id')? " AND assurances.id=".Request('assurance_id'):"") .
                                           " ) ON requirements.id = assurance_requirements_ct.requirement_id " .
                                      "WHERE " .  
                                        " requirements.requirement_type='extra' " .
                                       	" ;";
                    $signatures     = $DB->MultiQuery($viewsSigsQuery);
                    foreach($signatures as $row){
                        $checked = (empty($row['assurance_id'])) ? "" :  ' checked="checked" ';
                        echo('<li><div class="checkbox_container"><input type="checkbox" value="'.$row['id'].'_'.$viewId.'"'.$checked.$enabled.'></div><div class="description_container">'.$row['description'].'</div><div style="clear:both;"></div></li>');
                    }?>
                </ul>

            </div>
        </td>
        <td width="300px" valign="top">
            <div>
                <?php $data = getSignatureData($viewId); ?>
                <h1><?= $data['school_name'] ?></h1>

                <h2><a href="/a/post_views.php?id=<?= $viewId ?>"><?= $data['post_name'] ?></a></h2>
                <?php if ($viewId): ?>
                <?php
                $sigPermissionsQuery  = "SELECT role_id FROM users_roles WHERE user_id = '$userId'";
                $sigPermissionsResult = $DB->MultiQuery($sigPermissionsQuery);
                $sigPermissions       = array();
                foreach ($sigPermissionsResult as $result) {
                    $sigPermissions[$result['role_id']] = true;
                }

                $viewsSigsQuery = "SELECT `requirements`.`id`,
                                    `requirements`.description as 'name',
                                    `requirements`.required_role,
                                    `User`.`email`,
                                    CONCAT(`User`.`first_name`, ' ', `User`.`last_name`) AS `username`,
                                    `Signature`.`date_signed`, `schools`.`school_name`,
                                    `assurances`.`id` as 'assurance_id'
                                    FROM `requirements`
                                    LEFT JOIN 
                                    	(`assurance_requirements_ct` AS `Signature`  
                                    	INNER JOIN `assurances` ON `assurances`.`id`= `Signature`.`assurance_id` 
                                    	AND `assurances`.`vpost_view_id` = '" . $viewId . "' " .  
                                    	(isset($_GET['pdf_format'])?"":" AND `assurances`.`valid` = TRUE") .
                                        (Request('assurance_id')? " AND assurances.id=".Request('assurance_id'):"") .
                                    	" LEFT JOIN `users` AS `User` ON `Signature`.`user_id` = `User`.`id` 
                                    	LEFT JOIN `schools` ON `User`.`school_id`=`schools`.`id`
                                    	) ON `requirements`.`id` = `Signature`.`requirement_id` 
                                    WHERE requirements.requirement_type='stakeholder'";
                $signatures     = $DB->MultiQuery($viewsSigsQuery);
                $query = "SELECT id FROM assurances WHERE vpost_view_id = $viewId".(isset($_GET['pdf_format'])?";":" AND valid=TRUE;");
                $assurance_row = $DB->SingleQuery($query);
                $assurance_id = $assurance_row['id'];
                

                // If we need to group signatures, this is where we do it.
                $categories = array();
                foreach ($signatures as $signature) {
                    $categories[$signature['id']]['name'] = $signature['name'];
                    $categories[$signature['id']]['required_role'] = $signature['required_role'];
                    $categories[$signature['id']]['sigs'] = array();
                    if ($signature['email']) {
                        $sig['date_signed']                     = $signature['date_signed'];
                        $sig['email']                           = $signature['email'];
                        $sig['name']                            = $signature['username'];
                        $sig['school_name']                     = $signature['school_name'];
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
                        <ul style="margin-top:-5px;">
                        <?php if (!$sigCount && isset($sigPermissions[  $category['required_role'] ] )): ?>
                            <?php $signViewLinkUrl = '/a/post_views.php?assurance=1&action=sign&id=' . $viewId . '&category_id=' . $catId . '&assurance_id=' . $assurance_id; ?>
                            <a href="<?= $signViewLinkUrl ?>"><img src="/common/silk/script_edit.png" /></a> <a href="<?= $signViewLinkUrl ?>">Sign as this role.</a>
                        <?php endif; ?>
                    </p>
                    <?php if ($sigCount > 0): ?>
                        <?php foreach ($category['sigs'] as $sig): ?>
                            Signed by <?= $sig['name'] ?> 
                            on <?=  date_format(new DateTime($sig['date_signed']), 'Y-m-d')?><br /><?= $sig['school_name'] ?></li>
                            <?php endforeach; ?>
                    <?php else: ?>
                        <em>No signatures on file.</em>
                    <?php endif; ?>
                  </ul>
                    <?php endforeach; ?>
                <?php else: ?>&nbsp
                <?php endif;?>
            </div>
        </td>
    </tr>
</table>
<?php
}
else: ?>
<?php
    $reportData = getReportData();
?>
<table>
    <?php
    if(empty($reportData)){?>
        <tr class="postAssuranceReport schoolNameSpacer">
            <td colspan="6">&nbsp;</td>
        </tr>
        <tr class="postAssuranceReport schoolName">
            <td colspan="2">&nbsp;</td>
            <td colspan="4" style="padding:70px;"> <h2>Once POST Assurances Agreements have been created for your organizations POST Views a summary of your agreements will appear here.</h2></td>
        </tr>
    <?php } 
        foreach ($reportData as $schoolId => $schoolReport): ?>
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

<?php 
    if(isset($_GET['pdf_format'])){?> 
            </body>
            </html>         
    <?php } else {
        PrintFooter(); 
    }
