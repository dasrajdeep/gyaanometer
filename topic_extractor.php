<?php

function extract_named($text) {
	$parser='corenlp/stanford-corenlp-3.2.0.jar';

	chdir('corenlp');

	$tempfile='raw.txt';
	$file=fopen($tempfile,'w');
	fwrite($file,$text);
	fclose($file);

	$command='java -cp stanford-corenlp-3.2.0.jar:stanford-corenlp-3.2.0-models.jar:xom.jar:joda-time.jar:jollyday.jar-Xmx3g edu.stanford.nlp.pipeline.StanfordCoreNLP -annotators tokenize,ssplit,pos -file '.$tempfile;

	$result=shell_exec($command);
	
	$xml=simplexml_load_file('raw.txt.xml');
	
	chdir('..');
	
	$named=array();
	
	foreach($xml->document->sentences->sentence as $s) {
		foreach($s->tokens->token as $t) {
			if($t->POS=='NNP' || $t->POS=='NN') array_push($named,(string)$t->word[0]);
		}
	}
	
	return $named;
}

function extract_topics($posts) {
	$Wq=10;
	$Wa=1;
	
	$e=0.5;
	
	$named=array();
	$p=$posts;
	
	foreach($p['answers'] as $a) foreach($a as $b) if(!in_array($b,$named)) $named[$b]=0; 
	foreach($p['question'] as $b) if(!in_array($b,$named)) $named[$b]=0;
	
	foreach(array_keys($named) as $n) {
		foreach($p['answers'] as $a) foreach($a as $b) if($n==$b) $named[$n]+=$Wa; 
		foreach($p['question'] as $b) if($n==$b) $named[$n]+=$Wq; 
	}
	
	$total=0;
	foreach(array_values($named) as $v) $total+=$v;
	
	$ratios=array();
	
	//Compute ratios.
	foreach(array_keys($named) as $k) $ratios[$k]=($total==0)?0:$named[$k]/$total;
	
	$result=array();
	
	$max=0;
	$max_key='';
	foreach(array_keys($ratios) as $r) if($ratios[$r]>$max) {
		$max=$ratios[$r];
		$max_key=$r;
	}
	
	array_push($result,$max_key);
	
	foreach(array_keys($ratios) as $r) if(($max-$ratios[$r])<=$e) array_push($result,$r);
	
	return $result;
}

function content_analyze($text) {
	$c=curl_init();
	
	$url='http://query.yahooapis.com/v1/public/yql?q=';
	$query='select * from contentanalysis.analyze where text="'.$text.'"';
	
	curl_setopt($c,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($c,CURLOPT_URL,$url.urlencode($query).'&format=json');
	
	$result=curl_exec($c);
	
	curl_close($c);
	
	$entities=array();
	$x=json_decode($result,true);
	
	if(!isset($x['error'])) foreach($x['query']['results']['entities']['entity'] as $e) {
		$key=$e['text']['content'];
		$entities[$key]=$e['score'];
	}
	
	return $entities;
}

function classify_topics($qid) {
	$thres=0.1;
	
	$con=mysql_connect('localhost','root','retrograde');
	mysql_select_db('gyaanometer',$con);
	
	$query="select content,subject from yahoo_question where qid='%s'";
	$p=mysql_query(sprintf($query,$qid),$con);
	while($r=mysql_fetch_assoc($p)) $question=$r['subject'].$r['content'];
	
	$query="select content from yahoo_answer where qid='%s'";
	$p=mysql_query(sprintf($query,$qid),$con);
	$answers=array();
	while($r=mysql_fetch_assoc($p)) array_push($answers,$r['content']);
	
	$topics=content_analyze(strtolower($question.implode(' ',$answers)));
	$topics=array_filter($topics);
	
	if(count($topics)==0) return;
	
	$names=get_topics($topics,$thres);
	
	foreach($names as $t) @mysql_query(sprintf("insert into gyaanometer_topics (topic_name) values ('%s')",$t),$con);
	
	$query="insert into gyaanometer_topic_threads (tid,qid) values ((select tid from gyaanometer_topics where topic_name='%s'),'%s')";
	foreach($names as $t) mysql_query(sprintf($query,$t,$qid),$con);
	
	mysql_close($con);
}

function get_topics($topics,$thres) {
	$max=max(array_values($topics));
	$names=array();
	foreach(array_keys($topics) as $k) if(($topics[$k]-$max)<=$thres) array_push($names,$k);
	
	return $names;
}

?>
