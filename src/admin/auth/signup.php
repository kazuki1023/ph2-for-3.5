<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST["email"];
  $name = $_POST["name"];
  $password = $_POST["password"];
  $password_confirm = $_POST["password_confirm"];

  if ($password !== $password_confirm) {
    $message = "パスワードが一致しません";
  } else {
    $pdo = new PDO('mysql:host=db;dbname=posse', 'root', 'root');
    $sql = "SELECT * FROM users WHERE email = :email ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if (isset($user)) {
      header('Location: /admin/index.php');
    } else {
      try {
        $pdo->beginTransaction();

        $sql = "INSERT into users (name, email, password) values (:name, :email, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":password", password_hash($password, PASSWORD_DEFAULT));
        $stmt->bindValue(":name", $name);
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $lastInsertId = $pdo->lastInsertId();

        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $lastInsertId);
        $stmt->execute();
        $user = $stmt->fetch();

        $pdo->commit();
        $_SESSION['id'] = $user["id"];
        $_SESSION['message'] = "ユーザー登録に成功しました";
        header('Location: /admin/auth/signin.php');
      } catch (PDOException $e) {
        $pdo->rollBack();
        $message = $e->getMessage();
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POSSE ユーザー登録</title>
  <!-- スタイルシート読み込み -->
  <link rel="stylesheet" href="./../assets/styles/common.css">
  <link rel="stylesheet" href="./../admin.css">
  <!-- Google Fonts読み込み -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
  <header>
    <div>posse</div>
  </header>
  <div class="wrapper">
    <main>
      <div class="container">
        <h1 class="mb-4">ユーザー登録</h1>
        <?php if (isset($message)) { ?>
          <p><?= $message ?></p>
        <?php } ?>
        <form method="POST">
          <div class="mb-3">
            <label for="name" class="form-label">名前</label>
            <input type="text" name="name" id="name" class="form-control">
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" name="email" class="email form-control" value="" id="email">
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">パスワード</label>
            <input type="password" name="password" id="password" class="form-control">
          </div>
          <div class="mb-3">
            <label for="password_confirm" class="form-label">パスワード(確認)</label>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control">
          </div>
          <button type="submit" disabled class="btn submit">登録</button>
        </form>
      </div>
    </main>
  </div>
  <script>
    const submitButton = document.querySelector('.btn.submit')
    const inputDoms = Array.from(document.querySelectorAll('.form-control'))
    const password = document.querySelector('#password')
    const passwordConfirm = document.querySelector('#password_confirm')
    inputDoms.forEach(inpuDom => {
      inpuDom.addEventListener('input', event => {
        const isFilled = inputDoms.filter(d => d.value).length === inputDoms.length
        const isPasswordMatch = password.value === passwordConfirm.value
        submitButton.disabled = !(isFilled && isPasswordMatch)
      })
    })
  </script>
</body>

</html>