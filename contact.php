<?php 
require('function.php');

debug('「「「「「「「「「「「「「「「「「');
debug('「　お問い合わせページ');
debug('「「「「「「「「「「「「「「「「「');
debugLogStart();

$page_flag = 0; //入力画面

//確認ボタンが押された時
if(!empty($_POST['confirm'])) {
  debug('確認ボタンが押されました。');
  $name = $_POST['name'];
  $email = $_POST['email'];
  $content = $_POST['content'];
  
  //未入力チェック
  validRequired($name, 'name');
  validRequired($email, 'email');
  validRequired($content, 'content');
  
  if(empty($err_msg)) {
    debug('バリデーションOKです。');
    $page_flag = 1; //確認画面
    
    //戻るボタンが押された時
    if(!empty($_POST['back'])) {
      $page_flag = 0;
    }
  }
  
} elseif(!empty($_POST['submit'])) {
    debug('送信ボタンが押されました。');
    $name = $_POST['name'];
    $email = $_POST['email'];
    $content = $_POST['content'];

    //メールを送信
    $from = 'MyNotes<info@oh-mynotes.work>';
    $toYou = $email;
    $subjectToYou = '【MyNotes】お問い合わせありがとうございます';
    $commentToYou = <<<EOT
※このメールはお問い合わせフォームよりお問い合わせいただいた方へ自動送信しております。

{$name}様

この度はお問い合わせありがとうございます。
以下の内容で送信いたしました。

===================================================
[お名前]
{$name}

[メールアドレス]
{$email}

[お問い合わせ内容]
{$content}
===================================================


///////////////////////////////////
シンプルなメモアプリ MyNotes
URL     https://oh-mynotes.work
E-mail  info@oh-mynotes.work
///////////////////////////////////
EOT;
  
    
  
    $toMe = MyEmail;
    $subjectToMe = '【自動送信】お問い合わせがありました｜MyNotes';
    $commentToMe = <<<EOT
    
MyNotesのお問い合わせフォームから以下のお問い合わせが送信されました。
===================================================
[お名前]
{$name}

[メールアドレス]
{$email}

[お問い合わせ内容]
{$content}
===================================================
EOT;
    
    sendMail($from, $toYou, $subjectToYou, $commentToYou);
    sendMail($from, $toMe, $subjectToMe, $commentToMe);
    
    $page_flag = 2; //送信完了画面

} else {
    $page_flag = 0; //入力画面
};


$siteTitle = 'お問い合わせ';
require('head.php');

?>

<body>
  <?php require('header.php'); ?>
  <main class="content site-width">

    <?php if($page_flag === 1): ?>
    <form action="" method="post" class="main-form contact-form">
      <h2>お問い合わせ内容の確認</h2>
      <div class="contact-confirm-box">
        【お名前】
        <p><?php echo sanitize($name); ?></p><br>
        【メールアドレス】
        <p><?php echo sanitize($email); ?></p><br>
        【お問い合わせ内容】
       <p><?php echo nl2br(sanitize($content)); ?></p>
      </div>
      <p>上記の内容でよろしければ、送信ボタンを押してください。</p>
      <div class="top-btn-group">
        <input type="submit" name="back" value="戻る" class="btn btn-twin btn-trans btn-back">
        <input type="submit" name="submit" value="送信" class="btn btn-twin">
      </div>
      <input type="hidden" name="name" value="<?php echo sanitize($name); ?>">
      <input type="hidden" name="email" value="<?php echo sanitize($email); ?>">
      <input type="hidden" name="content" value="<?php echo sanitize($content); ?>">
      <br>
    </form>

    <?php elseif($page_flag === 2): ?>
    <div class="main-form contact-form">
      <h2>送信完了</h2>
      <p>お問い合わせいただきありがとうございます。</p>
      <p>ご入力いただいたメールアドレス宛に受付確認メールをお送りしましたのでご確認ください。</p>
      <br>
      <?php if(!empty($_SESSION['user_id'])): ?>
        <p class="center"><a href="note.php">ノートページに戻る</a></p>
      <?php else: ?>
        <p class="center"><a href="index.php">トップページに戻る</a></p>
      <?php endif; ?>
    </div>
    
    <?php else: ?>
    <form action="" method="post" class="main-form contact-form ">
      <h2>お問い合わせ</h2>
      <div class="msg">
        <?php if(!empty($err_msg['common'])) echo $err_msg['common'] ?>
      </div>
      <label class="form-label <?php //if(!empty($err_msg['name'])) echo 'err'; ?>">
        お名前 <span class="small">(必須)</span>
        <input type="text" name="name" value="<?php if(!empty($_POST['name'])) echo sanitize($_POST['name']); ?>" class="input-box" required>
        <div class="msg">
          <?php if(!empty($err_msg['name'])) echo $err_msg['name'] ?>
        </div>
      </label>
      <br>
      <label class="form-label <?php //if(!empty($err_msg['email'])) echo 'err'; ?>">
        メールアドレス <span class="small">(必須)</span>
        <input type="email" name="email" value="<?php if(!empty($_POST['email'])) echo sanitize($_POST['email']); ?>" class="input-box" required>
        <div class="msg">
          <?php if(!empty($err_msg['email'])) echo $err_msg['email'] ?>
        </div>
      </label>
      <br>
      <label class="form-label <?php //if(!empty($err_msg['content'])) echo 'err'; ?>" required>
      お問い合わせ内容 <span class="small">(必須)</span>
        <textarea name="content" id="" class="contact-content" required><?php if(!empty($_POST['content'])) echo sanitize($_POST['content']); ?></textarea>
        <div class="msg">
          <?php if(!empty($err_msg['content'])) echo $err_msg['content'] ?>
        </div>
      </label>
      <input type="submit" name="confirm" value="確認する" class="btn">

      <br>
      <?php if(!empty($_SESSION['user_id'])): ?>
        <p class="center"><a href="note.php">ノートページに戻る</a></p>
      <?php else: ?>
        <p class="center"><a href="index.php">トップページに戻る</a></p>
      <?php endif; ?>
    </form>
    <?php endif; ?>
  </main>

  <?php require('footer.php'); ?>
