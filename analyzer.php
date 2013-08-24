<?php

function calculate_score($uid,$topics) {print_r($topics);
	$len=count($topics);
	for($i=0;$i<$len;$i++) $topics[$i]=sprintf("'%s'",$topics[$i]);
	
	$con=mysql_connect('localhost','root','retrograde') or die('Unable to connect.');

	mysql_select_db('gyaanometer',$con);

	$query="select count(*) as c from yahoo_answer where uid='%s' and best_answer=1 and qid in (select qid from gyaanometer_topic_threads where tid in (select tid from gyaanometer_topics where topic_name in (%s)))";
	$p=mysql_query(sprintf($query,$uid,implode(',',$topics)),$con);

	//Tb
	while($r=mysql_fetch_assoc($p)) $tb=$r['c']; 

	$query="select count(*) as c from yahoo_answer where uid='%s' and qid in (select qid from gyaanometer_topic_threads where tid in (select tid from gyaanometer_topics where topic_name in (%s)))";
	$p=mysql_query(sprintf($query,$uid,implode(',',$topics)),$con);

	//Tt
	while($r=mysql_fetch_assoc($p)) $tt=$r['c'];

	/*$query="select count(distinct uid,qid) as c from yahoo_answer where uid='%s' and content like'%%%s%%%'";
	$p=mysql_query(sprintf($query,$uid,$topic),$con);
	
	while($r=mysql_fetch_assoc($p)) $at=$r['c'];*/
	
	$query="select qid,sum(votes_positive) as s from yahoo_answer where uid='%s' and qid in (select qid from gyaanometer_topic_threads where tid in (select tid from gyaanometer_topics where topic_name in (%s))) group by qid";
	$p=mysql_query(sprintf($query,$uid,implode(',',$topics)),$con);
	
	$votes=array();
	while($r=mysql_fetch_assoc($p)) $votes[$r['qid']]=$r['s'];
	
	$query="select qid,sum(votes_positive) as s from yahoo_answer where qid in (select qid from gyaanometer_topic_threads where tid in (select tid from gyaanometer_topics where topic_name in (%s))) group by qid";
	$p=mysql_query(sprintf($query,implode(',',$topics)),$con);
	
	$total_votes=array();
	while($r=mysql_fetch_assoc($p)) $total_votes[$r['qid']]=$r['s'];
	
	$votesum=0;
	foreach(array_keys($votes) as $q) $votesum+=($total_votes[$q]==0)?0:$votes[$q]/$total_votes[$q];
	
	mysql_close($con);
	
	$total_votes=count(array_keys($votes));
	$rating=($total_votes==0)?0:(($tb/$tt)+($votesum/$total_votes))/2;
	
	return $rating;
}

?>
