<?php

//Database connection.
$con=mysql_connect('localhost','root','retrograde') or die('Unable to connect to database.');

mysql_select_db('gyaanometer',$con);

$api_url='http://query.yahooapis.com/v1/public/yql?q=';

$c=curl_init();

curl_setopt($c,CURLOPT_RETURNTRANSFER,1);

//Users.
$topics=array('basketball','java');

foreach($topics as $t) {
	for($i=0;$i<10;$i++) {
		$query=sprintf('select id,UserId,Subject,Content from answers.search(%s,%s) where query="%s"',50*$i,50,$t);

		$request_url=$api_url.urlencode($query).'&format=json';
		
		curl_setopt($c,CURLOPT_URL,$request_url);
		$result=curl_exec($c);
		
		$x=json_decode($result,true);
		
		foreach($x['query']['results']['Question'] as $q) mysql_query(sprintf("insert into yahoo_question (qid,uid,subject,content) values ('%s','%s','%s','%s')",$q['id'],$q['UserId'],$q['Subject'],$q['Content']),$con);
	}
}

curl_close($c);
mysql_close($con);

?>
