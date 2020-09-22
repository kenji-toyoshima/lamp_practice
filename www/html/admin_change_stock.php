<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

//POSTで送られたtokenを取得
$token = get_post('token');

// トークンのチェック<function.php参照>
if(is_valid_csrf_token($token) === FALSE){
  set_error('不正なリクエストです。');
  redirect_to(LOGIN_URL);
}
//新たなtokenの生成
get_csrf_token();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}
//dsnを取得してからdbに接続、dbhを返り値とする　<db.php参照>　
$db = get_db_connect();
// $_SESSION['user_id']を取得後、usersテーブルから$user_idのuser_id,name,password,typeをセレクト文で取得
$user = get_login_user($db);

if(is_admin($user) === false){
  redirect_to(LOGIN_URL);
}

$item_id = get_post('item_id');
$stock = get_post('stock');
//update_item_stock: 在庫数の変更　成功すればTRUEを返す <item.php参照>
if(update_item_stock($db, $item_id, $stock)){
  set_message('在庫数を変更しました。');
} else {
  set_error('在庫数の変更に失敗しました。');
}

redirect_to(ADMIN_URL);