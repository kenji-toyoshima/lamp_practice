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

$db = get_db_connect();
$user = get_login_user($db);

//pulldownの値を取得
$pulldown = get_get('pulldown');

// 商品一覧用の商品データを取得(公開している商品のみ) <item.php参照>
$items = get_pulldown_items($db,$pulldown);

//HTMLエンティティ処理
$items = entity_assoc_array($items);

include_once VIEW_PATH . 'index_view.php';