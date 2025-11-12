<?php
session_start();
$host="localhost";
$user="root"; 
$password=""; 
$dbname="login_system"; 
        $conn = new mysqli($host,$user,$password,$dbname);
if ($conn->connect_error)
   {
    die("Connection failed: " . $conn->connect_error);
  }
$message = ""; 
if (isset($_GET['email']) && isset($_GET['password']) && isset($_GET['type'])) {
    $email = $_GET['email'];
           $password = $_GET['password'];
    $type = strtolower($_GET['type']);

     $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND type=?");
        $stmt->bind_param("ss",$email,$type);
      $stmt->execute();
    $result=$stmt->get_result();
    if ($result->num_rows > 0) {
        $row=$result->fetch_assoc();
       
        if ($password === $row['password']) {
            $_SESSION['user_id']=$row['id'];
             $_SESSION['email']= $row['email'];
            $_SESSION['type'] =$row['type'];

           if ($row['type'] == 'admin') {
      echo "
       <script>
        alert('Admin login successful!');
        window.location.href = 'admin.php';
        </script>
       ";
       exit();
}


            else 
              {
                 echo "
       <script>
        alert('Judge login successful!');
        window.location.href = 'judge.php';
        </script>
       ";
       exit();
            }
                } 
        else
           {
            $message = "<h3 style='color:red; text-align:center;'>Invalid password!</h3>";
           }
 } 
         else
             {
               $message = "<h3 style='color:red; text-align:center;'>No user found with this email and type!</h3>";
             }
    $stmt->close();
}
$conn->close();
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
    <form action="" method="Get">
           <h2 style="text-align: center; color: #00074f; font-family: Arial, sans-serif;">Welcome to the Login Page</h2>
      <input type="email" name="email" id="email" placeholder="Enter Your Email" required><br>  
      

           <input type="password" name="password" id="password" placeholder="Enter Your Password" required>
      <br>   
       <input type="radio" id="admin" name="type" value="admin" required> Admin
  <input type="radio" id="judge" name="type" value="judge" required> Judge
      <br>
       <input type="submit" id="btn" value="Login">
    </form>

    <?php 
    if(!empty($message)){
        echo $message;
    }
    ?>
  </div>
</body>
</html>
