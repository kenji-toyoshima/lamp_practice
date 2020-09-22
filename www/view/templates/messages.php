<!-- エラー配列を取得し、配列を初期化 -->
<?php foreach(get_errors() as $error){ ?>
  <!-- get_errorsから$errorを取り出し表示 -->
  <p class="alert alert-danger"><span><?php print $error; ?></span></p>
<?php } ?>
<!-- message配列を取得し、配列を初期化 -->
<?php foreach(get_messages() as $message){ ?>
  <!-- get_messages()から$messageを取り出し表示 -->
  <p class="alert alert-success"><span><?php print $message; ?></span></p>
<?php } ?>