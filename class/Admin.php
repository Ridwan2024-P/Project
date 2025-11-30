<?php
class User
{




    private $conn;

    public function __construct($db)
    {
        $this->conn = $db->getConnection();
    }

   
    public function addJudge($name, $email, $password)



    {
        
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            return "<script>alert('Judge with this email already exists!');</script>";
        }
        $stmt->close();

    
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

       
        $stmt = $this->conn->prepare("INSERT INTO users (name,email,password,type) VALUES (?,?,?,'judge')");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);
        $stmt->execute();
        $stmt->close();

        return "<script>alert('Judge Added Successfully!');</script>";




    }

    public function updateJudge($id, $name, $email, $password = null)
    {
        if (!empty($password)) {
           
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $hashedPassword, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        $stmt->execute();
        $stmt->close();

        return "<script>alert('Judge Updated Successfully!');</script>";





        
    }





















  
    public function getJudges()
    {
        return $this->conn->query("SELECT id, name, email, type FROM users WHERE type='judge' ORDER BY id DESC");
    }

  
    public function verifyLogin($email, $password)
    {
        $stmt = $this->conn->prepare("SELECT id, password FROM users WHERE email=? AND type='judge'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $stmt->close();
            return false;
        }

        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($password, $hashedPassword)) {
            return $id; 
        }






        return false; 
    }






































}
?>
