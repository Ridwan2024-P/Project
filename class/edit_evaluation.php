<?php
class JudgeEvaluation {
    private $db;

    public function __construct($db) {
        $this->db = $db->conn;
    }

    public function getAllEvaluations() {
        $sql = "SELECT * FROM evaluations ORDER BY group_number, judge_name";
        return $this->db->query($sql);
    }

    public function getEvaluation($group_number, $judge_name) {
        $stmt = $this->db->prepare("SELECT * FROM evaluations WHERE group_number=? AND judge_name=?");
        $stmt->bind_param("ss", $group_number, $judge_name);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function submitOrUpdateEvaluation($data) 
    
    {
        $stmt = $this->db->prepare("SELECT id FROM evaluations WHERE group_number=? AND judge_name=?");
        $stmt->bind_param("ss", $data['group_number'], $data['judge_name']);
        $stmt->execute();
        $res = $stmt->get_result();

        $total = $data['articulate'] + $data['tools'] + $data['presentation'] + $data['teamwork'];

        if ($res->num_rows > 0) {
           
            $stmt = $this->db->prepare("UPDATE evaluations 
                SET group_members=?, project_title=?, articulate=?, tools=?, presentation=?, teamwork=?, comments=?, total=?, updated_at=NOW()
                WHERE group_number=? AND judge_name=?");
            $stmt->bind_param("ssssiiiiiss",
                $data['group_members'], $data['project_title'],
                $data['articulate'], $data['tools'], $data['presentation'], $data['teamwork'],
                $data['comments'], $total, $data['group_number'], $data['judge_name']
            );
        } 
        
        
        
        else {
           
            $stmt = $this->db->prepare("INSERT INTO evaluations 
                (group_number, group_members, project_title, judge_name, articulate, tools, presentation, teamwork, comments, total, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssiiiiis",
                $data['group_number'], $data['group_members'], $data['project_title'], $data['judge_name'],
                $data['articulate'], $data['tools'], $data['presentation'], $data['teamwork'],
                $data['comments'], $total
            );
        }




        if ($stmt->execute()) 
            
            
            {
            $avgStmt = $this->db->prepare("SELECT AVG(total) as avg_score FROM evaluations WHERE group_number=?");
            $avgStmt->bind_param("s", $data['group_number']);
            $avgStmt->execute();
            $avgRes = $avgStmt->get_result()->fetch_assoc();
            return ['success' => true, 'average' => round($avgRes['avg_score'], 2)];
        } 
        
        else {
            return ['success' => false, 'error' => $stmt->error];
        }



    }




}
