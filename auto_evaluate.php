<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>自动评价</title>
</head>
<body>
	<form action="auto.php" method='post'>
	办公网帐号: <input type="text" name='username'/>
	密码: <input type="password" name='pwd'>
	<input type="submit" value= "提交">
	</form>
	<p>本插件为天津大学教师自动评价系统第一版，全部采取好评，用户帐号密码错误不给提示，多个老师讲同一课程的情况不能评价。。。</p>
	<p>有问题或者意见请提交到不知道在哪里的邮箱。</p>
</body>
</html>
<?php
function dd($ss){
	echo "<pre>";
	var_dump($ss);
	echo "</pre>";
}
function checkGet($arr){
	foreach($arr as $key){
		if(!isset($_REQUEST[$key]))
			return false;
	}
	return true;
}
$checkarr = array('username','pwd');
if(!checkGet($checkarr))
	exit;
$username = $_REQUEST['username'];
$pwd = $_REQUEST['pwd'];

$post_fields = 'uid='.$username.'&password='.$pwd;
$log_url = 'http://e.tju.edu.cn/Main/logon.do';
$cookie_file = tempnam('cache','cookie');
$ch = curl_init($log_url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
curl_exec($ch);
curl_close($ch);

$url = 'http://e.tju.edu.cn/Education/evaluate.do?todo=list';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
$result = curl_exec($ch);
curl_close($ch);
preg_match_all("/\"\.\/evaluate(.*?)\"/is",$result,$arr);
$result = $arr[0];
$urlRoot = 'http://e.tju.edu.cn/Education';
$post = array();
foreach($result as $key => &$value)
{
	$value = ltrim(trim($value,"\""),".");
	$value = $urlRoot.$value;
	preg_match_all('/.\d+/',$value,$arr);
	$post[] = $arr[0];
}

unset($value);
foreach($post as $key => &$value)
{
	foreach($value as $key1 => &$value1)
	{
		$value1 = ltrim($value1,"=");
	}
	unset($value1);
}
unset($value);
//dd($post);
$len = sizeof($post);
for($j=0;$j<$len;$j++){
	$testpost = $post[$j];
	$testurl = $result[$j];
	$ch = curl_init($testurl);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	$testresult = curl_exec($ch);
	curl_close($ch);
	preg_match("/(\d{6})(?=\))/",$testresult,$arr);
	$teacher = $arr[0];
	//dd($arr);
	$post_field = 'evaluate_type=1&lesson_id='.$testpost[0].'&course_id='.$testpost[2].'&union_id='.$testpost[1];
	for($i=1;$i<=4;$i++)
	{
		$tmp = $teacher.'_'.$i;
	$tmpadd = '&'.$tmp.'=100';
	$post_field .= $tmpadd;
}
$post_field .= '&sumScore=100&evaluateContent=';
//dd($post_field);
$ch = curl_init("http://e.tju.edu.cn/Education/toModule.do?prefix=/Education&page=/evaluate.do?todo=Submit");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_field);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
curl_exec($ch);
curl_close($ch);
}

echo "<script>alert('评价完成，快去看成绩吧');</script>";
?>

