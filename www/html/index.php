<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

// PDOを取得
$db = get_db_connect();

// PDOを利用してログインユーザーのデータを取得<user.php参照>
$user = get_login_user($db);

// 商品一覧用の商品データを取得(公開している商品のみ) <item.php参照>
$items = get_open_items($db);

//HTMLエンティティ処理
$items = entity_assoc_array($items);

// CSRFトークンの生成<function.php参照>
$token= get_csrf_token();

include_once VIEW_PATH . 'index_view.php';