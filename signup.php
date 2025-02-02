<?php

  include "config/functions.php";
  $userErr = $verPasErr = $emailErr = $phoneErr = $addressErr = $birthdayErr  = $passErr =''; 
  
?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.min.css">
    <title>Signup</title>
  </head>
  <body>
    <div class = "login__Cont">
      <div class="login_header">
        <h4>Please fill out your information</h4>
      </div>
      <form class="login__form" id="signup__form" method="post">

        <label class="label" for="user__input">Username: </label>
        <input class="user__input" required type="text" autocomplete="username" name="user__input" id="user__input" placeholder="Username">
        <div class="invalid-input__err">
          <?php echo($userErr); ?>
        </div>

        <label class="label" for="pass__input">Password: </label>
        <input class="pass__input" required type="password" name="pass__input" autocomplete="off" id="pass__input" placeholder="Password">
        <div class="invalid-input__err">
          <?php echo $passErr; ?>
        </div>

        <label class="label" for="pass-verify__input">Verify password: </label>
        <input class="pass__input" required type="password" name="pass-verify__input" autocomplete="off" id="pass-verify__input" placeholder="Enter password again">
        <div class="invalid-input__err">
          <?php echo $verPasErr; ?>
        </div>

        <label class="label" for="email__input">Email: </label>
        <input class="user__input" required type="email" name="email__input" autocomplete="off" id="email__input" placeholder="Email">
        <div class="invalid-input__err">
          <?php echo $emailErr; ?>
        </div>

        <label class="label" for="phone__input">Phone: </label>
        <input class="user__input" type="tel" name="phone__input" autocomplete="off" id="phone__input" placeholder="Telephone">
        <div class="invalid-input__err">
          <?php echo $phoneErr; ?>
        </div>

        <label class="label" for="address__input">Address</label>
        <input class="user__input" type="text" name="address__input" autocomplete="off" id="address__input" placeholder="Address">
        <div class="invalid-input__err">
          <?php echo $addressErr; ?>
        </div>
        
        <div class="formBtns__div">
          <button id ="submit_Btn" type="submit" class="btn">Register</button>
          <button type="reset" class="btn">Cansel</button>
        </div>
      </form>
      <p>have already an account? <a href="login.php">Sign in now!!</a></p>
    </div>
    <script src="/Jquerry/jquery-3.6.1.js"></script>
    <script src="signup.js"></script>
  </body>

  <!-- <script>
    // document.addEventListener('DOMContentLoaded', function(){
    
      //submit form
      // let form = document.getElementById('signup__form');
      let submitBtn = document.getElementById('submit_Btn');
      submitBtn.addEventListener('click', function(e){
        // form.addEventListener('submit', function(e){

        e.preventDefault();

        // var myHeaders = new Headers();
        // myHeaders.append("Content-Type", "multipart/form-data");

        // var formdata = new FormData();
        // formdata.append("userName", "dwcw");
        // formdata.append("pass", "ksdjfhblakf");
        // formdata.append("email", "sdfsdf@adfcsdf.com");
        // formdata.append("phone", "kjnkljn");
        // formdata.append("user_id", "lwekfn3245235");
        // formdata.append("address", "wer23423rwedq");
        // // let payload = JSON.stringify(formdata.get('userName'));
        // // console.log(payload);
        // var requestOptions = {
        //   method: 'POST',
        //   // headers: myHeaders,
        //   body: formdata
        //   // redirect: 'follow'
        // };

        // fetch("http://localhost:3000/api/post.php?method=POST", requestOptions)
        //   .then(response => response.json())
        //   .then(result => console.log(result))
        //   .catch(error => console.log('error', error));
        let formdata = new FormData(document.getElementById('signup__form'));
        let payload = JSON.stringify(Object.fromEntries(formdata));
        console.log(payload);
        
        fetch("http://localhost:3000/api/register.php", {
         
          method: 'POST',
          body: formdata
          
        })
        .then( 
          response => {return  response.json()
        })
        .then(
          data => { 
            if (data == "The user was Successfully added.") 
            {
              window.location.replace("http://localhost:3000/login.php"); 
            } else {
              console.log("unknown error: " + data);
            }
        })
        .catch(
          error => console.error('Error:', error)); 
        });
        

  </script> -->
  </html>