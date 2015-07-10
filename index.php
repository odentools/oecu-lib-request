<?php
require_once 'fpdf17/japanese.php';

include_once('common/common.php');   // 共通設定ファイル
include_once('simple_html_dom.php');	// Simple HTML DOM Parser
include_once('curl/curl.php');       // cURL ライブラリ

$bookData = array(
  'bbid' => $_REQUEST['bbid'],
  'id' => $_REQUEST['id'],
  'name' =>$_REQUEST['name'],
  'tel' => $_REQUEST['tel'],
  'cPos' => $_REQUEST['cPos']
);

library($bookData);


/* ---- */

function library($arr) {

  // サイトのベースURL
  $url = "http://oeculib.osakac.ac.jp/detail?bbid=".$arr['bbid'];

  $cookie = tmpfile();
  $html = docURL($url, $cookie);
  fclose($cookie);

  $htmlDom = str_get_html($html);

  // 書名
  foreach($htmlDom->find('#detailTblArea table tr') as $val) {
    if($val->children(0)->getAttribute('plaintext') == "書名/著者"){
      $result = $val->children(1)->getAttribute('plaintext');
      $result = str_replace("\t", "", $result);
      $result = str_replace("  ", "", $result);
      $bookName = $result;
      }
  }

//echo $bookName;

  // 請求記号
  $bookCode = $htmlDom->find('#holdBookTblArea table tr')[1]->children(1)->getAttribute('plaintext');
  $bookCode = preg_split('/\//', $bookCode);

  // 資料ID
  $bookId = $htmlDom->find('#holdBookTblArea table tr')[1]->children(2)->getAttribute('plaintext');

  $info = array(
   'id'=>$arr['id'],
   'name'=>$arr['name'],
   'tel'=>$arr['tel'],
   'bookName'=>$bookName,
   'bookId'=>$bookId,
   'bookCode1'=>@$bookCode[0],
   'bookCode2'=>@$bookCode[1],
   'bookCode3'=>@$bookCode[2],
   'cPos'=>$arr['cPos']
  );

  libraryPDF($info);

}


// 文字列をSJISに変換するsjis関数
function sjis($str) {
  mb_language("Japanese");
  return mb_convert_encoding($str, 'SJIS', 'UTF-8');
}

function libraryPDF($arr){

// A4サイズのPDF文書を準備
$pdf = new PDF_Japanese('P', 'mm', 'A4');
$pdf->AddSJISFont();

// 1テンプレートをインポート
$pdf->setSourceFile('20150710144928.pdf');
$importPage = $pdf->importPage(2);

$pdf->addPage();

// 2テンプレートをページに適用
$pdf->useTemplate($importPage, 0, 0);

// フォント設定変更
$pdf->setFont('SJIS', '', 10);


// 日付
$pdf->setXY(53, 5);
$pdf->write(18, sjis(date('Y')));
$pdf->setXY(65, 5);
$pdf->write(18, sjis(date('m')));
$pdf->setXY(74, 5);
$pdf->write(18, sjis(date('d')));

// 他館資料取寄
$pdf->setXY(11, 15);
$pdf->write(18, sjis('◯'));


// フォント設定変更
$pdf->setFont('SJIS', 'B', 16);

// 利用者ID
$pdf->setXY(25, 31);
$pdf->write(18, sjis($arr['id']));

// 氏名
$pdf->setXY(25, 46);
$pdf->write(18, sjis($arr['name']));

// 電話番号
$pdf->setXY(25, 56);
$pdf->write(18, sjis($arr['tel']));

// フォント設定変更
$pdf->setFont('SJIS', 'B', 7);

// 書名
$title = $arr['bookName'];
$pdf->setXY(25, 64);
$pdf->write(18, mb_substr(sjis($title), 0, 20));
$pdf->setXY(25, 67);
$pdf->write(18, mb_substr(sjis($title), 20, 20));


// フォント設定変更
$pdf->setFont('SJIS', '', 12);

// 資料ID
$pdf->setXY(25, 75);
$pdf->write(18, sjis($arr['bookId']));

// 請求記号1
$pdf->setXY(25, 84);
$pdf->write(18, sjis($arr['bookCode1']));

// 請求記号2
$pdf->setXY(42, 84);
$pdf->write(18, sjis($arr['bookCode2']));

// 請求記号3
$pdf->setXY(57, 84);
$pdf->write(18, sjis($arr['bookCode3']));

switch($arr['cPos']){

  case 'neyagawa':
   // 受け取り場所　寝屋川
   $pdf->setXY(33, 94);
   $pdf->write(18, sjis('◯'));
   break;
  
  default:
  case 'nawate':
    // 受け取り場所　畷
    $pdf->setXY(54, 94);
    $pdf->write(18, sjis('◯'));
    break;

  case 'ekimae':
    // 受け取り場所　駅前
    $pdf->setXY(69, 94);
    $pdf->write(18, sjis('◯'));
    break;

}

$pdf->addPage();

// 1テンプレートをインポート
$pdf->setSourceFile('20150710144928.pdf');
$importPage = $pdf->importPage(1);
$pdf->useTemplate($importPage, 120, 0);

// 最終結果を出力
$pdf->output();

}