<?php
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

//usersテーブルから$user_idのuser_id,name,password,typeをセレクト文で取得　失敗すればfalseを返す
function get_user($db, $user_id){
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      user_id = ?
    LIMIT 1
  ";
  $param = [$user_id];
  //$sqlを実行し、1行だけデータを取得 失敗すればfalseを返す<db.php参照>
  return fetch_query($db, $sql, $param);
}

//usersテーブルからname=$nameのuser_id,name,password,typeをセレクト文で取得　うまくいけばTRUEを返す
function get_user_by_name($db, $name){
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      name = ?
    LIMIT 1
  ";
  $param = [$name];
  //$sqlを実行し、1行だけデータを取得 失敗すればfalseを返す<db.php参照>
  return fetch_query($db, $sql, $param);
}

//$nameと$passwordが合っていればセッションにuser_idを追加し$userを返す　失敗すればfalseを返す
function login_as($db, $name, $password){
  //usersテーブルからname=$nameのuser_id,name,password,typeをセレクト文で取得
  $user = get_user_by_name($db, $name);
  if($user === false || $user['password'] !== $password){
    return false;
  }

  //$_SESSION[$name]に$valueを代入
  // function set_session($name, $value){
  //   $_SESSION[$name] = $value;
  // }

  set_session('user_id', $user['user_id']);
  return $user;
}

// $_SESSION['user_id']を取得後、usersテーブルから$user_idのuser_id,name,password,typeをセレクト文で取得
function get_login_user($db){
  //get_sessin($name)は$_SESSION[$name]に値が入っていれば_SEESION[$name]を返す。
  //入っていなければ初期化 <function.phpを参照＞
  $login_user_id = get_session('user_id');
  //usersテーブルから$user_idのuser_id,name,password,typeをセレクト文で取得
  return get_user($db, $login_user_id);
}

//ユーザー登録
function regist_user($db, $name, $password, $password_confirmation) {
  if(is_valid_user($name, $password, $password_confirmation) === false){
    return false;
  }
  //usersテーブルにnameとpasswordを追加
  return insert_user($db, $name, $password);
}

//ユーザータイプがadminであればTRUEを返す
function is_admin($user){
  //$user = get_user_by_name($db, $name); <user.php参照>
  //get_user_by_name: usersテーブルからname=$nameのuser_id,name,password,typeをセレクト文で取得
  //define('USER_TYPE_ADMIN', 1);
  return $user['type'] === USER_TYPE_ADMIN;
}

//$nameと$passwordが正当であればTRUE、そうでなければFALSEを返す
function is_valid_user($name, $password, $password_confirmation){
  // 短絡評価を避けるため一旦代入。
  $is_valid_user_name = is_valid_user_name($name);
  $is_valid_password = is_valid_password($password, $password_confirmation);
  return $is_valid_user_name && $is_valid_password ;
}

//$nameが正当であればTRUEを返す
function is_valid_user_name($name) {
  $is_valid = true;
  // $nameの文字数が条件を満たしていなければ　<function.phpを参照>
  if(is_valid_length($name, USER_NAME_LENGTH_MIN, USER_NAME_LENGTH_MAX) === false){
    //エラーにセット

    //$_SESSION['__errors']配列に$errorを追加 <function.phpを参照>
    // function set_error($error){
    //   $_SESSION['__errors'][] = $error;
    // }
    set_error('ユーザー名は'. USER_NAME_LENGTH_MIN . '文字以上、' . USER_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  
  if(is_alphanumeric($name) === false){
    set_error('ユーザー名は半角英数字で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

//$passwordが正当で確認用ともあっていればTRUEを返す
function is_valid_password($password, $password_confirmation){
  $is_valid = true;
  if(is_valid_length($password, USER_PASSWORD_LENGTH_MIN, USER_PASSWORD_LENGTH_MAX) === false){
    set_error('パスワードは'. USER_PASSWORD_LENGTH_MIN . '文字以上、' . USER_PASSWORD_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  if(is_alphanumeric($password) === false){
    set_error('パスワードは半角英数字で入力してください。');
    $is_valid = false;
  }
  if($password !== $password_confirmation){
    set_error('パスワードがパスワード(確認用)と一致しません。');
    $is_valid = false;
  }
  return $is_valid;
}

//usersテーブルにnameとpasswordを追加
function insert_user($db, $name, $password){
  $sql = "
    INSERT INTO
      users(name, password)
    VALUES (?,?);
  ";
  $param = [$name,$password];
  //SQLの実行＜db.php 参照＞
  return execute_query($db, $sql, $param);
}

