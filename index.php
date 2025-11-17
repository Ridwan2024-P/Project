<?php
session_start();
require_once "class/Database.php";
require_once "class/User.php";

$db=new Database()  ; $user=new User($db);
$message="";






if(isset($_GET['email'],$_GET['password'],$_GET['type'])) 
    {

    $email=$_GET['email'];

$password=$_GET['password'];
    $type=strtolower($_GET['type']);

    $message=$user->login($email,$password,$type);






}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login Page</title>
</head>
<body>
    <div id="form">
        <form action="" method="get">


            <h2 style="text-align: center; color: #00074f; font-family: Arial, sans-serif;">Welcome to the Login Page</h2>
           
 <input type="email" name="email" id="email" placeholder="enter your email" required>
 <br>
               <input type="password" name="password" id="password" placeholder="enter your password" required>
               <br>


            <input type="radio" id="admin" name="type" id="admin" value="admin" required>Admin

            <input type="radio" id="judge" name="type" id="judge" value="judge" required>Judge
            <br>
           
           
            <input type="submit" id="btn" value="Login">


        </form>

        <?php if(!empty($message)) 
            {
                
                echo $message;
                
                } 
                
                
                ?>

    </div>

    
</body>
</html>
