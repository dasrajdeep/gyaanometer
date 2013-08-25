<?php

$qid=$_GET['qid'];

$con=mysql_connect('localhost','root','retrograde');
mysql_select_db('gyaanometer',$con);

$p=mysql_query(sprintf("select subject,content from yahoo_question where qid='%s'",$qid),$con);
$q=mysql_fetch_assoc($p);

$p=mysql_query(sprintf("select content,uid from yahoo_answer where qid='%s'",$qid),$con);
$answers=array();
while($r=mysql_fetch_assoc($p)) array_push($answers,$r);

mysql_close($con);

require_once('../analyzer.php');
require_once('../topic_extractor.php');

$text=$q['subject'].' '.$q['content'];
foreach($answers as $a) $text.=' '.$a['content'];

$alz=content_analyze($text);
$entities=get_topics($alz,0.1);

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
		foreach($answers as $a) echo sprintf('<div class="ans">%s<br/><br/><i>by user %s</i><br/><br/><b><img src="../images/logo-resized.png" /> RatinG: %s%%</b></div>',$a['content'],$a['uid'],calculate_score($a['uid'],$entities)*100);
	?>
	</div>
	<div id="right"></div>
</body>	
<br/><br/>
<a href="index.php"><b>Go Home</b></a>
<div>
	<br/><br/>
	<hr/>
	<i>Designed By Moniods</i>
</div>
</html>
