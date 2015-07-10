<?php

/* docURL()
 * cUrl実行用関数
 */
function docURL($url, $cookie, $post = false) {

    // cURL初期化処理
    $ch = curl_init();

    // cURL各種パラメータ設定
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Cookie
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);

    // Post
    if($post != false) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    // cURL実行
    $output = curl_exec($ch);

    // cURL終了処理
    curl_close($ch);

    if ($output) {
        return $output;
    } else {
        throw new Exception("ページ取得失敗, $php_errormsg");
        return false;
    }

}

?>