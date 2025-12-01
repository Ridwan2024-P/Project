<?php
class JudgeEvaluation {
    private $conn;

    public function __construct($db) {
        
        $this->conn = $db->conn;
    }

    public function getEvaluation($group_number, $judge_name)
    
    
    {
        $stmt = $this->conn->prepare("SELECT * FROM evaluations WHERE group_number=? AND judge_name=? LIMIT 1");
        $stmt->bind_param("ss", $group_number, $judge_name);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();

        
    }

    public function getGroupInfo($group_number) 
    
    {
        $stmt = $this->conn->prepare("SELECT group_members, project_title FROM evaluations WHERE group_number=? LIMIT 1");
        $stmt->bind_param("s", $group_number);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

   public function getAllEvaluations()
   
   
   
   {
    return $this->conn->query("SELECT * FROM evaluations ORDER BY group_number DESC, judge_name");
}


    public function submitOrUpdateEvaluation($data)
    
    {
        $stmt = $this->conn->prepare("SELECT id FROM evaluations WHERE group_number=? AND judge_name=?");
        $stmt->bind_param("ss", $data['group_number'], $data['judge_name']);
        $stmt->execute();
        $res = $stmt->get_result();

        $total = $data['articulate'] + $data['tools'] + $data['presentation'] + $data['teamwork'];

        if ($res->num_rows > 0) {
           
            $stmt = $this->conn->prepare("UPDATE evaluations 
                SET group_members=?, project_title=?, articulate=?, tools=?, presentation=?, teamwork=?, comments=?, total=?, updated_at=NOW()
                WHERE group_number=? AND judge_name=?");
          
            $stmt->bind_param("ssiiiisiss",
                $data['group_members'], $data['project_title'],
                $data['articulate'], $data['tools'], $data['presentation'], $data['teamwork'],
                $data['comments'], $total, $data['group_number'], $data['judge_name']
            );
        } 
        
        else {
          
            $stmt = $this->conn->prepare("INSERT INTO evaluations 
                (group_number, group_members, project_title, judge_name, articulate, tools, presentation, teamwork, comments, total, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->bind_param("ssssiiiisi",
                $data['group_number'], $data['group_members'], $data['project_title'], $data['judge_name'],
                $data['articulate'], $data['tools'], $data['presentation'], $data['teamwork'],
                $data['comments'], $total
            );
        }

        if ($stmt->execute()) {
            $avgStmt = $this->conn->prepare("SELECT AVG(total) as avg_score FROM evaluations WHERE group_number=?");
            $avgStmt->bind_param("s", $data['group_number']);
            $avgStmt->execute();
            $avgRes = $avgStmt->get_result()->fetch_assoc();
            return ['success' => true, 'average' => round($avgRes['avg_score'], 2)];
        } else {
            return ['success' => false, 'error' => $stmt->error];
        }
    }





public function groupExists($group_number) {
    $sql = "SELECT id FROM evaluations WHERE group_number = ?";
    $stmt = $this->conn->prepare($sql);   
    $stmt->bind_param("s", $group_number);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}















    
}
