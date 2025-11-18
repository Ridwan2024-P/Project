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

  
   
    $stmt = $this->conn->prepare("INSERT INTO users (name,email,password,type) VALUES (?,?,?,'judge')");

    $stmt->bind_param("sss", $name, $email, $password);

    $stmt->execute();

    
    $stmt->close();

    return "<script>alert('Judge Added Successfully!');</script>";
}


    public function updateJudge($id, $name, $email, $password=null)
    
    {
        if(!empty($password))
             {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $id);
        }


         else 
            {

            $stmt = $this->conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $name, $email, $id);


        }



        $stmt->execute();
        $stmt->close();

return "<script>alert('Judge Updated Successfully!');</script>";
    }

    public function getJudges() 
    {


        return $this->conn->query("SELECT * FROM users WHERE type='judge' ORDER BY id DESC");



    }








}
