<pre>
<?php
session_start();

if(isset($_REQUEST['uid'])) {
	$con=mysql_connect('localhost','root','retrograde');
	mysql_select_db('gyaanometer',$con);
	
	$p=mysql_query(sprintf("select passwd from yahoo_user where uid='%s'",$_REQUEST['uid']),$con);
	if($r=mysql_fetch_assoc($p)) {
		$pass=$r['passwd'];
		if($pass==$_REQUEST['pass']) $_SESSION['uid']=$_REQUEST['uid'];
	}
	
	mysql_close($con);
} else if(isset($_REQUEST['logout'])) {
	session_destroy();
}

if(isset($_SESSION['uid'])) header('Location: view/');
else header('Location: view/login.php');
/*require_once('topic_extractor.php');
require_once('analyzer.php');

$qid='20070704130007AApBKjX';
$uid='N3jTHU2Eaa';

$con=mysql_connect('localhost','root','retrograde');
mysql_select_db('gyaanometer',$con);

$p=mysql_query(sprintf("select uid,content from yahoo_answer where qid='20070704130007AApBKjX'"),$con);
$data=array();
while($r=mysql_fetch_assoc($p)) array_push($data,$r);

$text='';

foreach($data as $d) {
	$text.=$d['content'];
}

$alz=content_analyze($text);
$entities=get_topics($alz,0.1);

foreach($data as $d) {
	$uid=$d['uid'];
	$score=calculate_score($uid,$entities);
	echo sprintf('<div>%s : %s</div>',$uid,$score*100);
}

if($con) mysql_close($con);*/

/*$con=mysql_connect('localhost','root','retrograde');
mysql_select_db('gyaanometer',$con);

$p=mysql_query("select qid,count(*) as c from yahoo_answer group by qid having c>=7",$con);
$qids=array();
while($r=mysql_fetch_assoc($p)) array_push($qids,$r['qid']);
	
mysql_close($con);	

foreach($qids as $q) classify_topics($q);*/

?>
</pre>
