<?php
class Evaluation
 {

private $conn;
public function __construct($db) 
{
        $this->conn = $db->getConnection();
    }
    public function updateEval($id, $total, $comments)
{
        $stmt = $this->conn->prepare("UPDATE evaluations SET total=?, comments=? WHERE id=?");
        $stmt->bind_param("dsi", $total, $comments, $id);

                 $stmt->execute();
        $stmt->close();

        return "<script>alert('Evaluation Updated Successfully!');</script>";




    }

    public function getAllEvals()
     {
        return $this->conn->query("SELECT * FROM evaluations ORDER BY created_at DESC");



    }







    
}
