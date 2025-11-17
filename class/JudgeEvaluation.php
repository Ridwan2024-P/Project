<?php
class JudgeEvaluation
 {
    private $conn;
    public function __construct($db)
     {
       
        $this->conn = $db->getConnection();

    }
    public function submitEvaluation($data)
     {
 $articulate_score = ($data['articulate']==='developing') ? rand(1,10) : rand(11,15);
        $tools_score = ($data['tools']==='developing') ? rand(1,10) : rand(11,15);

        $presentation_score = ($data['presentation']==='developing') ? rand(1,10) : rand(11,15);
        $teamwork_score = ($data['teamwork']=== 'developing') ? rand(1,10) : rand(11,15);

        $total = $articulate_score + $tools_score + $presentation_score + $teamwork_score;

        $stmt = $this->conn->prepare("INSERT INTO evaluations (group_members,group_number,project_title,judge_name,articulate_score,tools_score,presentation_score,teamwork_score,total,comments) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "ssssiiiiis",
            $data['group_members'],
            $data['group_number'],
            $data['project_title'],



            $data['judge_name'],
            $articulate_score,
            $tools_score,
            $presentation_score,
            $teamwork_score,
            $total,
            $data['comments']
        );


        $success = $stmt->execute();
        $stmt->close();

        if($success)
            
            {
            $average = $this->updateGroupAverage($data['group_number'], $data['group_members'], $data['project_title']);
            return ['success'=>true, 'average'=>$average];
        }
        
        else
            
            {
            return ['success'=>false, 'error'=>$this->conn->error];
        }




    }

    private function updateGroupAverage($group_number, $group_members, $project_title) 
    {


        $avg_query = $this->conn->prepare("SELECT AVG(total) AS avg_total FROM evaluations WHERE group_number=?");
        $avg_query->bind_param("s", $group_number);

        $avg_query->execute();
        $avg_result = $avg_query->get_result()->fetch_assoc();
                 $average = round($avg_result['avg_total'], 2);
                 $avg_query->close();

                $check = $this->conn->prepare("SELECT id FROM group_averages WHERE group_number=?");
                    $check->bind_param("s",$group_number);
            $check->execute();
        $check_result = $check->get_result();




        if($check_result->num_rows>0)
            {

            $update = $this->conn->prepare("UPDATE group_averages SET avg_score=?, updated_at=NOW() WHERE group_number=?");
            $update->bind_param("ds", $average, $group_number);
            $update->execute();
            $update->close();
        }
         
        else
             {
            $insert = $this->conn->prepare("INSERT INTO group_averages (group_number, group_members, project_title, avg_score) VALUES (?,?,?,?)");
            $insert->bind_param("sssd", $group_number, $group_members, $project_title, $average);
            $insert->execute();
            $insert->close();
        }

        $check->close();
        return $average;
    }

    public function getAllEvaluations() 
    
    {
        return $this->conn->query("SELECT * FROM evaluations ORDER BY created_at DESC");
    }
}
