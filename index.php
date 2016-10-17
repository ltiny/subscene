<?php
/* ------------------------------------------------------------------
- Tiny Subscene PHP Script
-
- Version : 1.0
-
- Author : Milad Maldar ( www.ltiny.ir )
-
- Download Link : http://ltiny.ir/subscene
------------------------------------------------------------------ */

$ckfile = '/tmp/simpleproxy-cookie-'.session_id();
$cookiedomain = str_replace("http://www.","",base64_decode('aHR0cHM6Ly9zdWJzY2VuZS5jb20='));
$cookiedomain = str_replace("https://www.","",$cookiedomain);
$cookiedomain = str_replace("www.","",$cookiedomain);
$url = base64_decode('aHR0cHM6Ly9zdWJzY2VuZS5jb20=') . $_SERVER['REQUEST_URI'];

if($_SERVER['HTTPS'] == 'on'){
	$mydomain = 'https://'.$_SERVER['HTTP_HOST'];
} else {
	$mydomain = 'http://'.$_SERVER['HTTP_HOST'];
}

$curlSession = curl_init();

curl_setopt ($curlSession, CURLOPT_URL, $url);
curl_setopt ($curlSession, CURLOPT_HEADER, 1);


if($_SERVER['REQUEST_METHOD'] == 'POST'){
	curl_setopt ($curlSession, CURLOPT_POST, 1);
	curl_setopt ($curlSession, CURLOPT_POSTFIELDS, $_POST);
}

curl_setopt($curlSession, CURLOPT_RETURNTRANSFER,1);
curl_setopt($curlSession, CURLOPT_TIMEOUT,30);
curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt ($curlSession, CURLOPT_COOKIEJAR, $ckfile); 
curl_setopt ($curlSession, CURLOPT_COOKIEFILE, $ckfile);

foreach($_COOKIE as $k=>$v){
	if(is_array($v)){
		$v = serialize($v);
	}
	curl_setopt($curlSession,CURLOPT_COOKIE,"$k=$v; domain=.$cookiedomain ; path=/");
}

$response = curl_exec ($curlSession);

if (curl_error($curlSession)){
        print curl_error($curlSession);
} else {

	$response = str_replace("HTTP/1.1 100 Continue\r\n\r\n","",$response);

	$ar = explode("\r\n\r\n", $response, 2); 


	$header = $ar[0];
	$body = $ar[1];

	$header_ar = split(chr(10),$header); 
	foreach($header_ar as $k=>$v){
		if(!preg_match("/^Transfer-Encoding/",$v)){
			$v = str_replace(base64_decode('aHR0cHM6Ly9zdWJzY2VuZS5jb20='),$mydomain,$v); //header rewrite if needed
			header(trim($v));
		}
	}

	$body = str_replace(base64_decode('aHR0cHM6Ly9zdWJzY2VuZS5jb20='),$mydomain,$body);

	print $body;

}

curl_close ($curlSession);
?>