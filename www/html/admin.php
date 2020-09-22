<?php
//設定ファイルとモデルを読み込み
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

//ログイン状態でなければLOGIN＿URLへリダイレクト　<const.php参照>
// function is_logined(){
//   return get_session('user_id') !== '';
// }
if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

//dsnを取得してからdbに接続、dbhを返り値とする　<db.php参照>　
$db = get_db_connect();

// $_SESSION['user_id']を取得後、usersテーブルから$user_idのuser_id,name,password,typeをセレクト文で取得<user.php参照>
$user = get_login_user($db);

//ユーザータイプがadminでなければLOGIN_URLにリダイレクト<user.php参照>
if(is_admin($user) === false){
  redirect_to(LOGIN_URL);
}

//itemsテーブルから全てのデータを取得 <item.php参照>
$items = get_all_items($db);
// 特殊文字をHTMLエンティティに変換
$items = entity_assoc_array($items);

// CSRFトークンの生成<function.php参照>
$token= get_csrf_token();

//viewを読み込み
include_once VIEW_PATH . '/admin_view.php';
