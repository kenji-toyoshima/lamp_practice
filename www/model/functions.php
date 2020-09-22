<?php

/**
* 特殊文字をHTMLエンティティに変換する
* @param str  $str 変換前文字
* @return str 変換後文字
*/
function entity_str($str) {
  if (is_numeric($str) === TRUE){
    return $str;
  }
  else{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}

/**
* 特殊文字をHTMLエンティティに変換する(2次元配列の値)
* @param array  $assoc_array 変換前配列
* @return array 変換後配列
*/
function entity_assoc_array($assoc_array) {
 
  foreach ($assoc_array as $key => $value) {
    foreach ($value as $keys => $values) {
      // 特殊文字をHTMLエンティティに変換
      $assoc_array[$key][$keys] = entity_str($values);
    }
  }
 
  return $assoc_array;
}


//$valueにhtmlspecialcharsを施す
function h($value) {
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function dd($var){
  var_dump($var);
  exit();
}

//引数のURLにリダイレクト
function redirect_to($url){
  header('Location: ' . $url);
  exit;
}
//$_GET[$name]に値が入っていればその値を返す。なければ初期化　
function get_get($name){
  if(isset($_GET[$name]) === true){
    return $_GET[$name];
  };
  return '';
}
//$_POST[$name]に値が入っていればその値を返す。なければ初期化　
function get_post($name){
  if(isset($_POST[$name]) === true){
    return $_POST[$name];
  };
  return '';
}

//$_FILE[$name]が存在していればその値を返す。なければ空配列を返す
function get_file($name){
  if(isset($_FILES[$name]) === true){
    return $_FILES[$name];
  };
  return array();
}

//$_SESSION[$name]に値が入っていれば_SEESION[$name]を返す。入っていなければ初期化
function get_session($name){
  if(isset($_SESSION[$name]) === true){
    return $_SESSION[$name];
  };
  return '';
}

//$_SESSION[$name]に$valueを代入
function set_session($name, $value){
  $_SESSION[$name] = $value;
}

//$_SESSION['__errors']配列に$errorを追加
function set_error($error){
  $_SESSION['__errors'][] = $error;
}

//エラー配列を取得し、配列を初期化
function get_errors(){
  //$_SESSION['__errors]に値が入っていればその値を返す
  $errors = get_session('__errors');
  //$errorが空であればarray()を返す
  if($errors === ''){
    return array();
  }
  //$_SESSION['__errors']にarray()を代入 初期化？
  set_session('__errors',  array());
  //$errors = get_session('__errors')を返す
  return $errors;
}

//$_SESSION['__errors']に値がある　かつ　$_SESSION['__errors']の個数が０でない時にTRUEを返す
function has_error(){
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}

//$_SESSION['__message']配列に$messageを追加
function set_message($message){
  $_SESSION['__messages'][] = $message;
}

//message配列を取得し、配列を初期化
function get_messages(){
  $messages = get_session('__messages');
  if($messages === ''){
    return array();
  }
  set_session('__messages',  array());
  return $messages;
}

// $_SESSION['user_id']に値が入っているときにTRUEを返す
function is_logined(){
  //get_session: $_SESSION['user_id']に値が入っていればその値を返す。入っていなければ初期化
  return get_session('user_id') !== '';
}

//画像のアップロードが有効だった場合、ランダムなファイル名を返す。そうでなければ''を返す
function get_upload_filename($file){
  //画像のアップロードがpostだった場合、TRUEを返す（post通信かどうか、ファイル形式があっているか）
  if(is_valid_upload_image($file) === false){
    return '';
  }
  //$file['tmp_name']のファイル形式を取得
  $mimetype = exif_imagetype($file['tmp_name']);
  //$extにファイルの拡張子を代入
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];
  // ランダムファイル名を返す（ユニークな値）
  return get_random_string() . '.' . $ext;
}

// ランダムな20文字を取得
function get_random_string($length = 20){
  // substr ( string $string , int $start [, int $length ] ) : string
  // 文字列 string の、start で指定された位置から length バイト分の文字列を返します。
  //base_convert:数値の基数を変換する 16進数から36進数
  //hash — ハッシュ値 (メッセージダイジェスト) を生成する
  //uniqid():13文字の文字列が生成される
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
}

// アップロードした画像を指定のフォルダに保存
function save_image($image, $filename){
  //move_uploaded_file ( string $filename , string $destination ) : bool
  //filename で指定されたファイルが 有効なアップロードファイルであるかどうかを確認します。 そのファイルが有効な場合、destination で指定したファイル名に移動されます。
  // define('IMAGE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/' );
  return move_uploaded_file($image['tmp_name'], IMAGE_DIR . $filename);
}

//$filenameが存在した場合、ファイルを削除　うまくいけばTRUEを返す
function delete_image($filename){
  if(file_exists(IMAGE_DIR . $filename) === true){
    //ファイルを削除する
    unlink(IMAGE_DIR . $filename);
    return true;
  }
  return false;
  
}

//PHP_INT_MAXはPHP がサポートする整数型の最大値
function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  //文字列の長さを取得
  $length = mb_strlen($string);

  return ($minimum_length <= $length) && ($length <= $maximum_length);
}

//$stringが'REGEXP_ALPHANUMERIC'半角英数字であればTRUEを返す
function is_alphanumeric($string){
  return is_valid_format($string, REGEXP_ALPHANUMERIC);
}

//$stringが'REGEXP_POSITIVE_INTEGER', '/\A([1-9][0-9]*|0)\z/'と合致していればTRUEを返す
function is_positive_integer($string){
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER);
}

//正規表現$formatが$stringに一致していればTRUEを返す
function is_valid_format($string, $format){
  return preg_match($format, $string) === 1;
}

//画像のアップロードが有効だった場合、TRUEを返す（post通信かどうか、ファイル形式があっているか）
function is_valid_upload_image($image){
  //post通信でアップロードされた場合はtrue、それ以外の方法でアップされている場合はfalseを返す
  //$_FILES['inputで指定したname']['tmp_name']：一時保存ファイル名
  if(is_uploaded_file($image['tmp_name']) === false){
    //$_SESSION['__errors']配列に$errorを追加
    set_error('ファイル形式が不正です。');
    return false;
  }
  //mine=Multipurpose Internet Mail Extensions
  //Exifとは「Exchangeable Image File Format」）の略語で画像のファイル形式のこと
  $mimetype = exif_imagetype($image['tmp_name']);
  // PERMITTED_IMAGE_TYPES <const.phpを参照>
  if(isset(PERMITTED_IMAGE_TYPES[$mimetype]) === false ){
    //implode関数：配列要素を文字列により連結する
    //$_SESSION['__errors']配列に$errorを追加
    set_error('ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。');
    return false;
  }
  return true;
}

// トークンの生成
function get_csrf_token(){
  // get_random_string()はユーザー定義関数。
  $token = get_random_string(30);
  // set_session()はユーザー定義関数。
  set_session('csrf_token', $token);
  return $token;
}

// トークンのチェック
function is_valid_csrf_token($token){
  if($token === '') {
    return false;
  }
  // csrf_tokenのセッションを取得
  return $token === get_session('csrf_token');
}

