<?php
class Group 

{
    private $conn;
    public function __construct($db)
 {
        $this->conn = $db->getConnection();
    }
    public function updateGroup($id, $number, $members, $title, $avg) 
    {
        
        $stmt = $this->conn->prepare("UPDATE group_averages SET group_number=?, group_members=?, project_title=?, avg_score=?, updated_at=NOW() WHERE id=?");
        $stmt->bind_param("sssdi", $number, $members, $title, $avg, $id);

        $stmt->execute();

        $stmt->close();

        return "<script>alert('Group Score Updated Successfully!');</script>";




    }

    public function getAllGroups()
    
    {

        return $this->conn->query("SELECT * FROM group_averages ORDER BY avg_score DESC");
    }



    
}
