<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'cart.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}
//$dsnを作成後、データベースに接続して$dbhを返す<db.php参照>
$db = get_db_connect();
// $_SESSION['user_id']を取得後、usersテーブルから$user_idのuser_id,name,password,typeをセレクト文で取得
$user = get_login_user($db);
//cartsテーブルにitemsテーブルを結合し、各種データを取得　成功したらTRUEを返す
$carts = get_user_carts($db, $user['user_id']);

// 特殊文字をHTMLエンティティに変換
$carts = entity_assoc_array($carts);
//合計金額を求める<cart.phpを参照>
$total_price = sum_carts($carts);

// CSRFトークンの生成<function.php参照>
$token= get_csrf_token();

include_once VIEW_PATH . 'cart_view.php';