<?php

//Database connection.
$con=mysql_connect('localhost','root','retrograde') or die('Unable to connect to database.');

mysql_select_db('gyaanometer',$con);

$api_url='http://query.yahooapis.com/v1/public/yql?q=';

$c=curl_init();

curl_setopt($c,CURLOPT_RETURNTRANSFER,1);

$p=mysql_query("select qid from yahoo_question limit 150 offset 0",$con);
$qs=array();
while($r=mysql_fetch_assoc($p)) array_push($qs,$r['qid']);

foreach($qs as $q) {
	$query=sprintf('select Answers from answers.getquestion where question_id="%s"',$q);

	$request_url=$api_url.urlencode($query).'&format=json';
	
	curl_setopt($c,CURLOPT_URL,$request_url);
	$result=curl_exec($c);
	
	$x=json_decode($result,true);
	
	if($x) foreach($x['query']['results']['Question']['Answers']['Answer'] as $a) {
		$best=0;
		if($a['Best']!=null) $best=1;
		mysql_query(sprintf("insert into yahoo_answer (uid,timestamp,qid,content,best_answer) values ('%s','%s','%s','%s','%s')",$a['UserId'],$a['Timestamp'],$q,$a['Content'],$best),$con);
	}
}

curl_close($c);
mysql_close($con);

?>
