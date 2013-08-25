<html>

<head>
	<title>GyaanoMeteR</title>
	<script src="scripts/jquery.js"></script>
	<link rel="stylesheet" type="text/css" href="../styles/global.css" />
</head>	
	
<body>
	<div id="header">
		<h1 align="center"><img src="../images/logo.png"/> for Yahoo! Answers</h1>
		<hr/>
		
		<h2 align="center"><img src="../images/check.png"/></h2>
		<div>
			<?php
			
			$con=mysql_connect('localhost','root','retrograde');
			mysql_select_db('gyaanometer',$con);
			
			$p=mysql_query("select qid,subject from yahoo_question where qid in (select qid from yahoo_answer group by qid having count(timestamp)>=7 order by RAND()) limit 10",$con);
			while($r=mysql_fetch_assoc($p)) {
				echo sprintf('<div class="qs"><a id="%s" href="question.php?qid=%s">%s</a></div>',$r['qid'],$r['qid'],$r['subject']);
			}
			
			mysql_close($con);
			
			?>
		</div>
	</div>
</body>	

</html>
