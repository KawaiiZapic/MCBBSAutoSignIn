<?php
$cookie = "";

if(checkSignIn($cookie)){die();}
$chal = passGeeTest($passKey);
if(!$chal){die();}
var_dump(signIn($cookie,"1","记上一笔，hold住我的快乐！",$chal));

function getFormHash(string $cookie) {
    $data = get("https://www.mcbbs.net/home.php?mod=spacecp&inajax=1",$cookie);
    preg_match("/<input type=\"hidden\" value=\"([a-z0-9]*?)\" name=\"formhash\" \/>/",$data,$match);
    if(!isset($match[1])){
        return false;
    }
    return $match[1];
}

function get(string $url,string $cookie,array $header = null){
    $c = curl_init();
    curl_setopt($c,CURLOPT_URL,$url);
    curl_setopt($c,CURLOPT_COOKIE,$cookie);
    curl_setopt($c, CURLOPT_HEADER, 0);
    curl_setopt($c,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($c,CURLOPT_HTTPHEADER, [
        "Referer: https://www.mcbbs.net/plugin.php?id=dc_signin",
        "Connection: closed",
        "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36"
    ]);
    if(!is_null($header)) {
        curl_setopt($c,CURLOPT_HTTPHEADER, $header);
    }
    $x = curl_exec($c);
    curl_close($c);
    return $x;
}
function post(string $url,string $cookie, array $data,array $header = null) {
    $c = curl_init();
    curl_setopt($c,CURLOPT_URL,$url);
    curl_setopt($c,CURLOPT_COOKIE,$cookie);
    curl_setopt($c, CURLOPT_HEADER, 0);
    curl_setopt($c,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c,CURLOPT_HTTPHEADER, [
        "Referer: https://www.mcbbs.net/plugin.php?id=dc_signin",
        "Connection: closed",
        "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36"
    ]);
    if(!is_null($header)) {
        curl_setopt($c,CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    $x = curl_exec($c);
    curl_close($c);
    return $x;
}

function checkSignIn (string $cookie) {
    $signUrl = "https://www.mcbbs.net/plugin.php?id=dc_signin:sign&inajax=1";
    $check = get($signUrl, $cookie);
    if(!$check || preg_match("/您今日已经签过到/",$check)) {
        return true;
    }
    return false;
}

function signIn(string $cookie, string $emote,string $content,array $chal) {
    $signUrl = "https://www.mcbbs.net/plugin.php?id=dc_signin:sign&inajax=1";
    $hash = getFormHash($cookie);
    if(!$hash){return false;}
    $result = post($signUrl, $cookie, [
        "formhash" => $hash,
        "signsubmit" => "yes",
        "handlekey" => "signin",
        "emotid" => $emote,
        "referer" => "https://www.mcbbs.net/plugin.php?id=dc_signin",
        "content" => $content,
        "geetest_challenge" => $chal[0],
        "geetest_validate" => $chal[1],
        "geetest_seccode" => $chal[1] . "|jordan"
    ]);
    if(!preg_match("/签到成功/",$result)){
        echo $result.PHP_EOL;
        return false;
    }
    return true;
}
