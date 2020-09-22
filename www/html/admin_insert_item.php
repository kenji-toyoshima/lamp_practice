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

//CSRFセッションを空にする
set_session('csrf_token', array());

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

$name = get_post('name');
$price = get_post('price');
$status = get_post('status');
$stock = get_post('stock');

$image = get_file('image');

//itemsテーブルに$name, $price, $stock, $filename, $statusを追加 成功すればTRUEを返す<item.phpを参照>
if(regist_item($db, $name, $price, $stock, $status, $image)){
  set_message('商品を登録しました。');
}else {
  set_error('商品の登録に失敗しました。');
}


redirect_to(ADMIN_URL);