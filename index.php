<pre>
<?php

require_once('topic_extractor.php');
require_once('analyzer.php');

/*$qid='20070704130007AApBKjX';
$uid='N3jTHU2Eaa';

*/
$con=mysql_connect('localhost','root','retrograde');
mysql_select_db('gyaanometer',$con);

$p=mysql_query(sprintf("select * from yahoo_answer where qid='20070704130007AApBKjX'"),$con);
$data=array();
while($r=mysql_fetch_assoc($p)) array_push($data,$r);
//print_r($data);die();
//echo $data[0]['q.content'].'<br/>';

$text='';

foreach($data as $d) {
	$text.=$d['content'];
}

$entities=get_topics(content_analyze($text),0.1);

foreach($data as $d) {
	$uid=$d['uid'];
	$score=calculate_score($uid,$entities);
	echo sprintf('<div>%s : %s</div>',$uid,$score);
}

mysql_close($con);

/*$con=mysql_connect('localhost','root','retrograde');
mysql_select_db('gyaanometer',$con);

$p=mysql_query("select qid from yahoo_question",$con);
$qids=array();
while($r=mysql_fetch_assoc($p)) array_push($qids,$r['qid']);
	
mysql_close($con);	
	
foreach($qids as $qid) classify_topics($qid);*/

?>
</pre>
