<?php

session_start();
if(!isset($_SESSION['user_id']))
     {
    header("Location: index.php");
    exit();
}
require_once "class/Database.php";
require_once "class/JudgeEvaluation.php";
$db=new Database();
$eval=new JudgeEvaluation($db);
$message="";





if($_SERVER["REQUEST_METHOD"] === "POST") 
    {
    $data = [
        'group_members' => $_POST['group_members'] ?? '',
        'group_number' => $_POST['group_number'] ?? '',
        'project_title' => $_POST['project_title'] ?? '',
        'judge_name' => $_POST['judge_name'] ?? '',
        'articulate' => $_POST['articulate'] ?? '',
        'tools' => $_POST['tools'] ?? '',
        'presentation' => $_POST['presentation'] ?? '',
        'teamwork' => $_POST['teamwork'] ?? '',
        'comments' => $_POST['comments'] ?? '',
    ];

   
 $result = $eval->submitEvaluation($data);


    if($result['success'])
        {
        $message = "<p style='color:green; text-align:center;'>Evaluation submitted successfully! Group average updated to <b>{$result['average']}</b>.</p>";
    } 
    else
         {
        $message = "<p style='color:red; text-align:center;'>Error: {$result['error']}</p>";
    }
}

$eval_result = $eval->getAllEvaluations();




?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Judge Evaluation Form</title>
<link rel="stylesheet" href="judge.css">
</head>
<body>

<div id="judge">
    <div class="logout" style="display:flex; justify-content:end; width:100%; margin-bottom:10px;">
        <form method="POST" action="logout.php">
            <button type="submit">Logout</button>
        </form>
    </div>

    <?php if($message != "") echo "<div>{$message}</div>"; ?>
    <h2 style="text-align:center;color: #00074f; font-size:35px;">Computer Science Project</h2>

    <form method="POST" action="">


        <label>Group Members:</label>
        <input type="text" name="group_members" required>
        <label>Group Number:</label>
        <input type="text" name="group_number" required>
        <label>Project Title:</label>
        <input type="text" name="project_title" required>
        <table>
            <thead>

                <tr>
                    <th>Criteria</th>
                    <th>Developing (0–10)</th>
                    <th>Accomplished (11–15)</th>
                </tr>
</thead>
<tbody>
<?php
$criteria = [
    'Articulate requirements',
    'Choose appropriate tools and methods',
    'Give clear and coherent presentation',
    'Functioned well as a team'
];

$names = ['articulate','tools','presentation','teamwork'];

foreach($criteria as $i => $c){
    $name = $names[$i];

    $dev_id = $name . "_dev";
    $acc_id = $name . "_acc";

    echo "
    <tr>
        <td>{$c}</td>

        <td>
            <input type='radio' name='{$name}_select' onclick=\"
                document.getElementById('{$dev_id}').style.display='block';
                document.getElementById('{$acc_id}').style.display='none';
                document.getElementById('{$acc_id}').value='';
            \">

            <input type='number'
                   name='{$name}_dev'
                   id='{$dev_id}'
                   min='0'
                   max='10'
                   style='display:none; margin-top:8px; width:90%;'
                   required>
        </td>

        <td>
            <input type='radio' name='{$name}_select' onclick=\"
                document.getElementById('{$acc_id}').style.display='block';
                document.getElementById('{$dev_id}').style.display='none';
                document.getElementById('{$dev_id}').value='';
            \">

            <input type='number'
                   name='{$name}_acc'
                   id='{$acc_id}'
                   min='11'
                   max='15'
                   style='display:none; margin-top:8px;width:90%;'
                   required>
        </td>
    </tr>";
}
?>

</tbody>

        </table>
        <style>input[type="number"] {
    width: 60px;
}
</style>

        <label>Judge Name:</label>
        <input type="text" name="judge_name" required>

        <label>Comments:</label>
        <textarea name="comments" placeholder="Optional comments..."></textarea>

        <button type="submit">Submit Grades</button>
    </form>

    <hr>
    <h3>Submitted Evaluations</h3>
    <table>
        <thead>
            <tr>
                <th>Group</th>
                <th>Judge</th>
                <th>Total</th>
                <th>Average</th>
                <th>Comments</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>




            <?php
            if($eval_result && $eval_result->num_rows>0)
                
                
                {
                while($row=$eval_result->fetch_assoc()){
                    $avg_res = $db->getConnection()->query("SELECT avg_score FROM group_averages WHERE group_number='{$row['group_number']}'");
                    $avg_val = ($avg_res && $avg_res->num_rows>0) ? $avg_res->fetch_assoc()['avg_score'] : '-';
                    echo "<tr>
                        <td>".htmlspecialchars($row['group_number'])."</td>
                        <td>".htmlspecialchars($row['judge_name'])."</td>
                        <td>{$row['total']}</td>
                        <td>{$avg_val}</td>
                        <td>".htmlspecialchars($row['comments'])."</td>
                        <td>{$row['created_at']}</td>
                    </tr>";
                }
            }
            
            else 
            {
                echo "<tr><td colspan='6'>No evaluations yet.</td></tr>";
            }
            ?>



        </tbody>
    </table>






</div>

</body>
</html>
