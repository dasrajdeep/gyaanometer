<?php

session_start();

$uid=$_SESSION['uid'];
$time=time();
$qid=$_REQUEST['qid'];
$content=$_REQUEST['content'];

$con=mysql_connect('localhost','root','retrograde');
mysql_select_db('gyaanometer',$con);

mysql_query(sprintf("insert into yahoo_answer (uid,qid,timestamp,content) values ('%s','%s','%s','%s')",$uid,$qid,$time,addslashes($content)),$con);

mysql_close($con);	

header('Location: question.php?qid='.$qid);

?>
