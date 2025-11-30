<?php
class User {
    private $conn;
    public function __construct($db)
     {
        $this->conn=$db->getConnection();
    }

    public function login($email, $password, $type)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email=? AND type=?");
        $stmt->bind_param("ss", $email, $type);
 $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            
            if (password_verify($password, $row['password'])) {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['type'] = $row['type'];

                if ($row['type'] == "admin") 
                    
                    {
                    header("Location: admin.php");
                    exit();
                } 
                
                
                else
                    
                    {
                    header("Location: judge.php");
                    exit();
                }

            }
            
            
            
            else 
                
                
                {
                return "<h3 style='color:red; text-align:center;'>Wrong password!</h3>";
            }

        }
        
        
        else
        
        {
            return "<h3 style='color:red; text-align:center;'>No user found</h3>";
        }



        $stmt->close();








    }


















    
}
