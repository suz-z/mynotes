
 <div class="container">
  <header class="header">
    <div class="header-container site-width">
      <h1><a href="index.php" class="site-title font-noto">MyNotes</a></h1>
      <nav>
        <ul class="nav nav-pc">
          <?php if(empty($_SESSION['user_id'])) { ?>
          <li>
          <a href="signup.php"><i class="fas fa-user-plus"></i><br>ユーザー登録</a></li>
          <li><a href="login.php"><i class="fas fa-sign-in-alt"></i><br>ログイン</a></li>
          <?php } else { ?>
          <li><a href="setting.php"><i class="fas fa-user-cog"></i><br>マイメニュー</a></li>
          <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><br>ログアウト</a></li>
          <?php } ?>
        </ul>
        <ul class="nav nav-sp">
          <?php if(empty($_SESSION['user_id'])) { ?>
          <li><a href="signup.php"><i class="fas fa-user-plus"></i></a></li>
          <li><a href="login.php"><i class="fas fa-sign-in-alt"></i></a></li>
          <?php } else { ?>
          <li><a href="searchSp.php"><i class="fas fa-search"></i></a></li>
          <li><a href="setting.php"><i class="fas fa-user-cog"></i></a></li>
          <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
          <?php } ?>
        </ul>
      </nav>
    </div>
  </header>
  <div class="header-padding"></div>
