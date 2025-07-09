<?php
include "config/functions.php";

// if ($_SERVER["REQUEST_METHOD"] == "POST"){

//   $user = test_input($_POST['user__input']);
//   $pass = test_input($_POST['pass__input']);

//   $new_user = new UserController;

//   if ($new_user->check_password($user, $pass)){

//     header("Location: index.php");
//     die();
//   } else {
//     echo "Wrong password !!"; //errormodule
   

//   }
  

// }


  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.min.css">
    <script src="Jquerry/jquery-3.6.1.js"></script>
    <title>Login</title>
  </head>
  <body>
    <div class = "login__Cont">
      <div class="login_header">
        <h4>Please Login using your Username and Password</h4>
      </div>
      <form class="login__form" method="POST">
        <label class="label" for="user__input">Username: </label>
        <input class="user__input" type="text" autocomplete="username" name="user__input" id="user__input" placeholder="Username">
        <label class="label" for="pass__input">Password: </label>
        <input class="pass__input" type="password" name="pass__input" autocomplete="current-password" id="pass__input" placeholder="Password">
        <div class="formBtns__div">
          <button type="submit" class="btn" id="submitLogin">Login</button>
          <button type="reset" class="btn">Cansel</button>
        </div>
      </form>
      <p>have you forgotten your password? <a href="passReset.php">Reset now!!</a></p>
      <p>don't have an account? <a href="signup.php">Sign up now!!</a></p>
    </div>
    <script src="login.js"></script>
  </body>
  </html>
