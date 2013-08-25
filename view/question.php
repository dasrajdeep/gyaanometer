<?php
error_reporting(0);
$qid=$_GET['qid'];

$con=mysql_connect('localhost','root','retrograde');
mysql_select_db('gyaanometer',$con);

$p=mysql_query(sprintf("select subject,content from yahoo_question where qid='%s'",$qid),$con);
$q=mysql_fetch_assoc($p);

$p=mysql_query(sprintf("select content,uid from yahoo_answer where qid='%s'",$qid),$con);
$answers=array();
while($r=mysql_fetch_assoc($p)) array_push($answers,$r);

require_once('../analyzer.php');
require_once('../topic_extractor.php');

$text=$q['subject'].' '.$q['content'];
foreach($answers as $a) $text.=' '.$a['content'];

$alz=content_analyze($text);
//Plan-B

if(!$alz || count($alz)==0) {
	$p=mysql_query(sprintf("select topic_name from gyaanometer_topics where tid in (select tid from gyaanometer_topic_threads where qid='%s')",$qid),$con);
	$entities=array();
	while($r=mysql_fetch_assoc($p)) array_push($entities,$r['topic_name']);
	echo sprintf('<script>alert("No internet connection. Working offline.");</script>');
} else {
	$entities=get_topics($alz,0.1);
}

if(count($entities)==0) echo sprintf('<script>alert("No internet connection. Please check your connection and try again.");window.location="index.php";</script>');

mysql_close($con);

?>
<html>

<head>
	<title>GyaanoMeteR</title>
	<script src="scripts/jquery.js"></script>
	<link rel="stylesheet" type="text/css" href="../styles/global.css" />
</head>	
	
<body>
	<h1><img src="../images/logo.png" /> for Yahoo! Answers</h1>
	
	<div id="left"></div>
	<div id="middle">
	<?php
		echo sprintf('<h1>%s</h1>',$q['subject']);
		echo sprintf('<h3>%s</h3>',$q['content']);
		?>
	<form action="post.php" method="post">
	<textarea name="content"></textarea>
	<br/>
	<input type="hidden" name="qid" value="<?php echo $qid; ?>" />
	<input type="submit" value="Post Answer" />
	</form>
		<?php
		foreach($answers as $a) echo sprintf('<div class="ans">%s<br/><br/><i>by user %s</i><br/><br/><b><img src="../images/logo-resized.png" /> RatinG: %s%%</b></div>',$a['content'],$a['uid'],round(calculate_score($a['uid'],$entities)*100,2));
	?>
	</div>
	<div id="right"></div>
</body>	
<div style="clear:both"></div>
<br/><br/>
<a href="index.php"><b>Go Home</b></a>
<div>
	<br/><br/>
	<hr/>
	<i>Designed By Moniods</i>
</div>
</html>
