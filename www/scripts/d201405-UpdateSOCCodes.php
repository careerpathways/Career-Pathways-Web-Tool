<?php
include('scriptinc.php');

function updateContent($content) {
    global $replacementArray;
    preg_match_all('/occ=(\d+)/',$content,$matches);
    $result = $content;
    foreach($matches[1] as $socCode) {
        $code = $socCode;
        if (array_key_exists($socCode,$replacementArray)) {
            $code = $replacementArray[$socCode];
        }
        $pattern = '|http://[^"]*occ='.$socCode.'[^"]*|';
        $newURL = BuildOlmisLink($socCode); //"https://new.qualityinfo.org/jc-oprof/?at=1&t1={$socCode}~{$socCode}~4101000000~0";
        $result = preg_replace($pattern,$newURL,$result);
    }
    return $result;
}

$row = 1;
$changeList = array();
$commaList = array();
passthru('pwd');
$OKCodes = array('119011','151071','339032','339099');
$replacementArray = array(
    '119011'=>'119013',
    '151071'=>'151142',
    '339032'=>'339093',
    '339099'=>'339093'
);
if (($handle = fopen("scripts/2014_Soc_Changes.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
        $num = count($data);
        $row++;
        $changeList[$data[2]] = $data;
        $commaList[] = str_replace('-','',$data[2]);
    }
    fclose($handle);

    $drawingList = array();
    $drawingListOK = array();
    $drawingListNOTOK = array();
    foreach($changeList as $id=>$data) {
        $searchFor = str_replace('-','',$id);
        print "-----------------------------------------------------\n";
        print "Testing for $id\n";
        $sql = <<<SQL
          SELECT drawing_main.id, drawings.id as version,objects.content, objects.id as object_id
            FROM drawing_main INNER JOIN drawings ON drawing_main.id = drawings.parent_id
                              INNER JOIN objects ON drawings.id = objects.drawing_id
            WHERE objects.content LIKE "%occ={$searchFor}%"
            ;
SQL;
        $sql = <<<SQL
          SELECT objects.content, objects.id as object_id,objects.drawing_id as id, objects.drawing_id as version
            FROM objects
            WHERE objects.content LIKE "%occ=%"
            ;
SQL;

        $objects = $DB->MultiQuery($sql);
        //$objects = $DB->MultiQuery('SELECT * FROM objects WHERE content LIKE "%occ='.$searchFor.'%";');
        $count = 0;
        print 'Drawing Ids: ';
        foreach($objects as $o)
        {
            print "{$o['id']}:{$o['version']}:{$o['object_id']}, ";
            //var_dump($o);
            $content = unserialize($o['content']);
            //Get list of urls to be changed
            $matches='';
            $content['config']['content'] = updateContent($content['config']['content']);
            $content['config']['content_html'] = updateContent($content['config']['content_html']);

            $setContent = serialize($content);
            $setContent = $DB->Safe($setContent);
            $sql = <<<SQL
            UPDATE objects SET content='{$setContent}' WHERE id={$o['object_id']};
SQL;
            //print $sql;
            $objects = $DB->MultiQuery($sql);

            //var_dump($result);
            //die();
            $drawingList[] = $o['id'];
            $count++;
            if (in_array($searchFor,$OKCodes)) {
                $drawingListOK[] = $o['id'];
            } else {
                $drawingListNOTOK[] = $o['id'];
            }
        }
        print "\n";
        print "Occrances Count $count\n";

        print "-----------------------------------------------------\n";

    }
    print "Unique Drawings Count:" . sizeof($drawingList) . "\n";
    print "Unique OK Count:" . sizeof($drawingListOK) . "\n";
    print "Unique NOT OK Count:" . sizeof($drawingListNOTOK) . "\n";
    $inBoth = 0;
    foreach($drawingListOK as $id) {
        if(in_array($id,$drawingListNOTOK)) {
            $inBoth++;
        }
    }
    print "In Both:" . $inBoth . "\n";
}