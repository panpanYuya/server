<?php
$json = file_get_contents("php://input");

$post = json_decode($json, true);

// // var_dump($post);

// $retArray = array(
//   'rType'=> 'success'
//   , 'message'=> $post['name'] // <- 送信されてきた「テストデータ」という文字列がこれで取得出来る
// );

// header('Content-type: application/json');
// echo json_encode($retArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:*");
header("Access-Control-Allow-Headers:content-type");

//エスケープ処理やデータチェックを行う関数のファイルの読み込み
require './libs/functions.php';

//お問い合わせ日時を日本時間に
date_default_timezone_set('Asia/Tokyo');

//POSTされたデータがあれば変数に格納、なければ NULL（変数の初期化）
$companyname = isset( $post[ 'companyname' ] ) ? $post[ 'companyname' ] : NULL;
$name = isset( $post[ 'name' ] ) ? $post[ 'name' ] : NULL;
$furigana = isset( $post[ 'furigana' ] ) ? $post[ 'furigana' ] : NULL;
$email = isset( $post[ 'email' ] ) ? $post[ 'email' ] : NULL;
$email_check = isset( $post[ 'email_check' ] ) ? $post[ 'email_check' ] : NULL;
$tel = isset( $post[ 'tel' ] ) ? $post[ 'tel' ] : NULL;
$subject = 'サイトからの問い合わせ';
$contact_type = isset( $post['contact_type']) ? $post['contact_type'] : NULL;
$contents = isset( $post[ 'contents' ] ) ? $post[ 'contents' ] : NULL;

echo 'success!!';

//POSTされたデータを整形（前後にあるホワイトスペースを削除）
$companyname = trim( $companyname );
$name = trim( $name );
$furigana = trim( $furigana );
$email = trim( $email );
$email_check = trim( $email_check );
$tel = trim( $tel );
$contact_type = trim( $contact_type );
$contents = trim( $contents );

//POSTされたデータに不正な値がないかを別途定義した checkInput() 関数で検証
$post = checkInput( $post );

//エラーメッセージを保存する配列の初期化
$error = array();

$return_body = ['status' => 200, 'error' => ''];

//値の検証
if ( preg_match( '/\A[[:^cntrl:]]{0,50}\z/u', $companyname ) == 0 ) {
  $error['companyname'] = '*会社名／団体名は50文字以内でお願いします。';
  $returnBody = ['error' => '*会社名／団体名は50文字以内でお願いします。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  return;

}
if ( $name == '' ) {
  $error['name'] = '*お名前は必須項目です。';
  //制御文字でないことと文字数をチェック
} else if ( preg_match( '/\A[[:^cntrl:]]{1,30}\z/u', $name ) == 0 ) {
  $error['name'] = '*お名前は30文字以内でお願いします。';
  $returnBody = ['error' => '*お名前は30文字以内でお願いします。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  
  return;
}
if ( $furigana  == '' ) {
  $error['furigana '] = '*お名前は必須項目です。';
  //制御文字でないことと文字数をチェック
} else if ( preg_match( '/\A[[:^cntrl:]]{1,50}\z/u', $furigana  ) == 0 ) {
  $error['furigana '] = '*お名前は50文字以内でお願いします。';
  $returnBody = ['error' => '*お名前は50文字以内でお願いします。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  
  return ;
}
if ( $email == '' ) {
  $error['email'] = '*メールアドレスは必須です。';
} else { //メールアドレスを正規表現でチェック
  $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/uiD';
  if ( !preg_match( $pattern, $email ) ) {
    $error['email'] = '*メールアドレスの形式が正しくありません。';
    $returnBody = ['error' => '*メールアドレスの形式が正しくありません。'];
    $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
    $http_response_code = 403;
    $GLOBALS['http_response_code'] = $http_response_code;
    header($jsonstr . $http_response_code);

    return ;
  }
}
if ( $email_check == '' ) {
  $error['email_check'] = '*確認用メールアドレスは必須です。';
} else { //メールアドレスを正規表現でチェック
  if ( $email_check !== $email ) {
    $error['email_check'] = '*メールアドレスが一致しません。';
    $returnBody = ['error' => '*メールアドレスが一致しません。'];
    $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
    $http_response_code = 403;
    $GLOBALS['http_response_code'] = $http_response_code;
    header($jsonstr . $http_response_code);
    
    return ;
  }
}
if ( preg_match( '/\A[[:^cntrl:]]{0,30}\z/u', $tel ) == 0 ) {
  $error['tel'] = '*電話番号は30文字以内でお願いします。';
  $returnBody = ['error' => '*電話番号は30文字以内でお願いします。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  
  return ;
}
if ( $tel != '' && preg_match("/^0\d{9,10}$/", $tel ) == 0 ) {
  $error['tel_format'] = '*電話番号の形式が正しくありません。';
  $returnBody = ['error' => '*電話番号の形式が正しくありません。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  
  return ;
}
if(!isset($post['contact_type'])){
  $error['contact_type'] = '*お問い合わせジャンルは必須項目です。';
  $returnBody = ['error' => '*お問い合わせジャンルは必須項目です。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  
  return ;
}
if ( $contents == '' ) {
  $error['contents'] = '*内容は必須項目です。';
  $returnBody = ['error' => '*内容は必須項目です。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  
  return ;

  //制御文字（タブ、復帰、改行を除く）でないことと文字数をチェック
} else if ( preg_match( '/\A[\r\n\t[:^cntrl:]]{1,1050}\z/u', $contents ) == 0 ) {
  $error['contents'] = '*内容は1000文字以内でお願いします。';
  $returnBody = ['error' => '*内容は1000文字以内でお願いします。'];
  $jsonstr =  json_encode($returnBody, JSON_UNESCAPED_UNICODE);
  $http_response_code = 403;
  $GLOBALS['http_response_code'] = $http_response_code;
  header($jsonstr . $http_response_code);
  
  return ;
}

//エラーがなく且つ POST でのリクエストの場合
if (empty($error) && $_SERVER['REQUEST_METHOD']==='POST') {
  //メールアドレス等を記述したファイルの読み込み
  require './libs/mailvars.php';

  //メール本文の組み立て。値は h() でエスケープ処理
  $mail_body = 'ホームページからのお問い合わせ' . "\n\n";
  $mail_body .=  "会社名／団体名： " . h($companyname) . "\n";
  $mail_body .=  "お名前： " . h($name) . "（" . h($furigana) . "）" . "\n";
  $mail_body .=  "Email： " . h($email) . "\n"  ;
  $mail_body .=  "お電話番号： " . h($tel) . "\n\n" ;
  $mail_body .=   "お問い合わせジャンル ：" . $contact_type;
  $mail_body .=   "\n" ;
  $mail_body .=  "＜お問い合わせ内容＞" . "\n" . h($contents);

  //-------- sendmail を使ったメールの送信処理 ------------

  //メールの宛先（名前<メールアドレス> の形式）。値は mailvars.php に記載
  $mailTo = mb_encode_mimeheader(MAIL_TO_NAME) ."<" . MAIL_TO. ">";

  //Return-Pathに指定するメールアドレス
  $returnMail = MAIL_RETURN_PATH; //
  //mbstringの日本語設定
  mb_language( 'ja' );
  mb_internal_encoding( 'UTF-8' );

  // 送信者情報（From ヘッダー）の設定
  $header = "From: " . mb_encode_mimeheader($name) ."<" . $email. ">\n";
  $header .= "Cc: " . mb_encode_mimeheader(MAIL_CC_NAME) ."<" . MAIL_CC.">\n";
  $header .= "Bcc: <" . MAIL_BCC.">";

  //メールの送信
  //セーフモードがOnの場合は第5引数が使えない
  if ( ini_get( 'safe_mode' ) ) {
    $result = mb_send_mail( $mailTo, $subject, $mail_body, $header );
  } else {
    $result = mb_send_mail( $mailTo, $subject, $mail_body, $header, '-f' . $returnMail );
  }

  //メール送信の結果判定
  if ( $result ) {
    //自動返信メール
    //ヘッダー情報
    $ar_header = "MIME-Version: 1.0\n";
    $ar_header .= "From: " . mb_encode_mimeheader( AUTO_REPLY_NAME ) . " <" . MAIL_TO . ">\n";
    $ar_header .= "Reply-To: " . mb_encode_mimeheader( AUTO_REPLY_NAME ) . " <" . MAIL_TO . ">\n";
    //件名
    $ar_subject = 'お問い合わせ自動返信メール';
    //本文
    $ar_body = $name." 様\n\n";
    $ar_body .= "この度は、お問い合わせ頂き誠にありがとうございます。" . "\n\n";
    $ar_body .= "下記の内容でお問い合わせを受け付けました。\n\n";
    $ar_body .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
    $ar_body .= "お名前：" . $name . "（" . $furigana . "）"  . "\n";
    $ar_body .= "メールアドレス：" . $email . "\n";
    $ar_body .= "お電話番号： " . $tel . "\n\n" ;
    $ar_body .=   "お問い合わせジャンル ：" . $contact_type;
    $ar_body .=   "\n" ;
    $ar_body .=  "＜お問い合わせ内容＞" . "\n" . h($contents);

    if ( ini_get( 'safe_mode' ) ) {
      $result2 = mb_send_mail( $email, $ar_subject, $ar_body , $ar_header );
    } else {
      $result2 = mb_send_mail( $email, $ar_subject, $ar_body , $ar_header , '-f' . $returnMail );
    }

    //空の配列を代入し、すべてのPOST変数を消去
    $post = array();

    //変数の値も初期化
    $companyname = '';
    $name = '';
    $furigana = '';
    $email = '';
    $email_check = '';
    $tel = '';
    $contact_type = '';
    $contents = '';

    //再読み込みによる二重送信の防止
    $params = '?result='. $result .'&result2=' . $result2;
    $url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://').$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
    header('Location:' . $url . $params);
    exit;
  }
}
?>
