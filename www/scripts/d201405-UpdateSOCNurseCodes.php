<?php
include('scriptinc.php');

function updateContent($content,$searchFor,$replaceWith) {
    global $replacementArray;
    $result = $content;
    $pattern = '|'.$searchFor.'~|';
    $result = preg_replace($pattern,"{$replaceWith}~",$result);

    return $result;
}

$row = 1;
$changeList = array();
$commaList = array();
passthru('pwd');
$replacementArray = array(
    '291111'=>'291141',
);
$changeList = array(
    '291111'=>'291141',
);

$drawingList = array();
$drawingListOK = array();
$drawingListNOTOK = array();
foreach($changeList as $id=>$data) {
    $searchFor = str_replace('-','',$id);
    print "-----------------------------------------------------\n";
    print "Testing for $id\n";
    $sql = <<<SQL
      SELECT objects.content, objects.id as object_id,objects.drawing_id as id, objects.drawing_id as version
        FROM objects
        WHERE objects.content LIKE "%t1=%{$searchFor}%"
        ;
SQL;

    $objects = $DB->MultiQuery($sql);
    $count = 0;
    print 'Drawing Ids: ';
    foreach($objects as $o)
    {
        print "{$o['id']}:{$o['version']}:{$o['object_id']}, ";
        $content = unserialize($o['content']);
        //Get list of urls to be changed
        $matches='';
        $content['config']['content'] = updateContent($content['config']['content'],$id,$data);
        $content['config']['content_html'] = updateContent($content['config']['content_html'],$id,$data);

        $setContent = serialize($content);
        $setContent = $DB->Safe($setContent);
        $sql = <<<SQL
        UPDATE objects SET content='{$setContent}' WHERE id={$o['object_id']};
SQL;
        $objects = $DB->MultiQuery($sql);

        $drawingList[] = $o['id'];
        $count++;
    }
    print "\n";

    $sql = <<<SQL

      UPDATE olmis_links SET olmis_id = '291141'  WHERE olmis_id = '291111';
SQL;
    $DB->MultiQuery($sql);


    print "-----------------------------------------------------\n";

}