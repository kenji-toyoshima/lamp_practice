<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';

session_start();
// $_SESSION['user_id']に値が入っているときzHOME_URLにリダイレクト <function.phpを参照>
if(is_logined() === true){
  redirect_to(HOME_URL);
}

// CSRFトークンの生成<function.php参照>
$token= get_csrf_token();

include_once VIEW_PATH . 'login_view.php';