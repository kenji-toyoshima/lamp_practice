<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

//POSTで送られたtokenを取得
$token = get_post('token');

// トークンのチェック<function.php参照>
if(is_valid_csrf_token($token) === false){
  set_error('不正なリクエストです。');
  redirect_to(LOGIN_URL);
}

//CSRFセッションを空にする
set_session('csrf_token', array());

//ログイン状態でなければLOGIN＿URLへリダイレクト　<const.php参照>
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

//dsnを取得してからdbに接続、dbhを返り値とする　<db.php参照>　
$db = get_db_connect();

// $_SESSION['user_id']を取得後、usersテーブルから$user_idのuser_id,name,password,typeをセレクト文で取得
$user = get_login_user($db);

//ユーザータイプがadminでなければLOGIN_URLにリダイレクト
if(is_admin($user) === false){
  redirect_to(LOGIN_URL);
}

$item_id = get_post('item_id');
$changes_to = get_post('changes_to');

//$changes_toが'open'であればステータスを公開に変更
if($changes_to === 'open'){
  //ステータスを変更<item.phpを参照>
  update_item_status($db, $item_id, ITEM_STATUS_OPEN);
  //$_SESSION['__message']配列に$messageを追加<function.phpを参照>
  set_message('ステータスを変更しました。');
//$changes_toが'open'であればステータスを非公開に変更
}else if($changes_to === 'close'){
  update_item_status($db, $item_id, ITEM_STATUS_CLOSE);
  set_message('ステータスを変更しました。');
//$changes_toがopenかcloseではない場合
}else {
  set_error('不正なリクエストです。');
}


redirect_to(ADMIN_URL);