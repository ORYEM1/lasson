<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Lang" content="en">
<meta name="author" content="">
<meta http-equiv="Reply-to" content="@.com">
<meta name="generator" content="PhpED 8.0">
<meta name="description" content="">
<meta name="keywords" content="">
<meta name="creation-date" content="09/06/2012">
<meta name="revisit-after" content="15 days">


<link href="/resources/login/style.css" rel="stylesheet">
<script src="/resources/lib/jquery-3.7.1.min.js"></script>
<script src="/resources/login/javascript.js"></script>

<title>Login</title>
    <link rel="icon" href="/resources/images/icon.jpg">
</head>
<body>

<!--<div id="header">
    <div id="logo">
    <img src="/resources/images/logo.png" alt="logo" style="width:80px;height:80px;">
    </div>
    <div id="menu">
        <ul>
            <li><a href="/resources/login/login.html">Home</a></li>
            <li><a href="/resources/login/login.html">Sign In</a></li>
            <li><a href="/resources/login/signup.html">Sign Up</a></li>
        </ul>
    </div>


</div>-->
<div class="login-page">

  <div class="form">
      <p> <h1 style="color: lightseagreen">LOGIN</h1> </p>
      <hr style="color: lightseagreen;">
      <div id="logo">
          <img src="/resources/images/logo.png" alt="Logo" style="width:100px;height:100px;border-radius:50%;border: black">
      </div>

    <form class="login-form" method="post" action="">
      <input type="text" name="username" value="<?php echo set_value('username'); ?>" required placeholder="USERNAME"/>
      <input type="password" name="password" id="password" value="<?php echo set_value('password');?>" required placeholder="PASSWORD"/>
        <input type="checkbox" id="toggle_password" > <label for="toggle_password">Show Password</label>

        <label> <a href="" style="align-items: end;margin-right: 10px">Forgot Password?</a> </label>
        <br><br>
      <button>LOGIN</button>
        <p>You do not have an account? <a href="">sign up</a> </p>
    </form>
    <div class="error"><?php if(isset($error)) echo $error; ?></div>
  </div>
</div>


</body>
</html>
