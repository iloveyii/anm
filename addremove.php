<?php session_start();
	// check if logged out button pressed
	if (isset($_SESSION['email']) AND isset($_POST['logout'])) {
		unset($_SESSION['email']);
		session_destroy();
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="cache-control" content="no-cache,no-store,must-revalidate">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<title>P1 Assignment 03</title>
<script src="js/jquery-1.7.min.js" type="text/javascript"></script>
<script src="js/ajax.js" type="text/javascript"></script>
<link href="css/table.css" rel="stylesheet" type="text/css" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="wrapper">

  <div class="content"> 
    <div class="header"></div>
	<div class="body">



<header>
    <nav class="clearfix">
        <ul>
            <li class="active" id="home"><a href="index.php"><span>Hem</span></a></li>
            <li><a href="index.php">Probed Devices</a></li>
            <li><a href="addremove.php">Add/Remove Devices</a></li>
            <li><a href="admin.php">User Admin</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
        <div id="socialmedia">
            <a target="_blank" href="/rss"><img alt="RSS" src="img/icon-rss.png"></a>
            <a target="_blank" href="/twitter"><img alt="Twitter" src="img/icon-twitter.png"></a>
            <a target="_blank" href="/facebook"><img alt="Facebook" src="img/icon-facebook.png"></a>
        </div>
    </nav>
    <div id="search">  </div>
</header>
<div class="trial">
    <div class="controlpanel">
    <a class="alpha" title="Show Probed Devices" href="index.php"></a>
    <!-- Show these links only for authorized users  --> 
    <?php if (isset($_SESSION['email'])) {echo '<a title="Add/Remove Devices" class="bravo" href="addremove.php"></a>';}?>
    <?php if (isset($_SESSION['admin'])) {echo '<a title="Admin" class="charlie" href="admin.php"></a>';}?> 
    </div>
    <div style="float:right; text-align:right; padding-right:15px; padding-top: 20px;">
      <form action="" method="post" id="login_form">
        <?php if (isset($_SESSION['email'])) { ?>
            <input name="logout" type="hidden" value="logout" />
            <a class="button button_gray button_lock" href="javascript:{}" onclick="document.getElementById('login_form').submit(); return false;">Logout</a>
        <?php } else { ?>
            <input name="login" type="hidden" value="login" />
            <label>Email</label> 
            <input type="text" value="" size="30" placeholder="Email" name="email" id="user_email" class="form_inputs">
            <input type="password" value="" size="30" placeholder="Password" name="password" id="user_email" class="form_inputs">
            <a class="button button_gray button_lock" href="javascript:{}" onclick="document.getElementById('login_form').submit(); return false;">Login</a> 
            <a class="button button_orange" href="register.php" target="_new">Sign Up</a>
        <?php }?>
      </form>
     </div> 
</div>
  
<div class="news_update"><span class="heading">Device Management</span>
	  <?php
        require_once 'php/registerclass.php';
        require_once 'conf.php';
        if (isset($_POST['email']) AND isset($_POST['login'])) {
            $login = new Registration($dbHost, $dbName, $dbUser, $dbPass);
            $login->doLogin($_POST['email'], $_POST['password']); 
        } 
      ?>

</div>
<fieldset><legend></legend>         
       
   <div class="tableDiv" style="border: display: inline-block; margin: 0 auto 20px; 
    width: 927px;">
  	<?php
  		require_once 'php/addremoveclass.php' ;
		$addremove = new AddRemoveDevice($dbHost, $dbName, $dbUser, $dbPass);
		$table= $addremove->showTable();
	?>
   </div>
   <div id="result">...</div>
</fieldset>

    
    </div> <!-- Body -->
      <div class="footer">
      	<div class="footer-content">
            <div>
                <h5>Site</h5>
                <ul>
                    <li><a href="">Download</a></li>
                    <li><a href="">Channels</a></li>
                    <li><a href="">Blog</a></li>
                </ul>
            </div>
            <div>
                <h5>Company</h5>
                <ul>
                    <li><a href="">About</a></li>
                    <li><a href="">Partners</a></li>
                    <li><a href="">Contact</a></li>
                </ul>
            </div>
            <div>
                <h5>Help</h5>
                <ul>
                    <li><a href="">FAQ</a></li>
                    <li><a href="">Tutorials</a></li>
                    <li>
                        <a href="" id="">Feedback</a>
                    </li>
                </ul>
            </div>
            <div class="last-child">
                <h5>Legal</h5>
                <ul>
                    <li><a href="">Terms of Service</a></li>
                    <li><a href="">Privacy Policy</a></li>
                    <li><a href="">Copyright Policy</a></li>
                </ul>
            </div>

        </div>
      </div>
  </div> <!-- content -->
</div> <!-- wrapper -->

</body>
</html>
