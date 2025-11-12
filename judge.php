<?php
session_start();
if(!isset($_SESSION['user_id'])) 
    {
header("Location: index.php");
exit();
}
$host="localhost";
$user="root";
$pass="";
$db="login_system";
$conn =new mysqli($host,$user,$pass,$db);
if($conn->connect_error) 
    {
 die("connection failed:".$conn->connect_error);
 }



$message="";
if($_SERVER["REQUEST_METHOD"]==="POST") {
    $group_members=$_POST['group_members'] ?? '';
    $group_number=$_POST['group_number'] ?? '';
    $project_title=$_POST['project_title'] ?? '';
$judge_name=$_POST['judge_name'] ?? '';
    $comments=$_POST['comments'] ?? '';
    $articulate = $_POST['articulate'] ?? '';
 $tools = $_POST['tools'] ?? '';
    $presentation = $_POST['presentation'] ?? '';
    $teamwork = $_POST['teamwork'] ?? '';
       $articulate_score=($articulate==='developing') ? rand(1,10):rand(11,15);
 $tools_score =($tools==='developing') ? rand(1,10) : rand(11,15);
    $presentation_score=($presentation === 'developing') ? rand(1,10) : rand(11,15);
 $teamwork_score =($teamwork ==='developing') ? rand(1,10) : rand(11,15);

$total =$articulate_score+$tools_score+$presentation_score+$teamwork_score;

     $stmt = $conn->prepare("INSERT INTO evaluations (group_members,group_number,project_title,judge_name,articulate_score,tools_score,presentation_score,teamwork_score, total,comments)VALUES (?,?, ?,?,? ,?,?,?,?,?)");
      $stmt->bind_param("ssssiiiiis", 
        $group_members, $group_number,$project_title,$judge_name,
        $articulate_score, $tools_score,  $presentation_score,$teamwork_score,$total,$comments
    );
    if($stmt->execute()) 
        {
       
$avg_query=$conn->prepare("SELECT AVG(total) AS avg_total FROM evaluations WHERE group_number=?");
              $avg_query->bind_param("s", $group_number);
        $avg_query->execute();
        $avg_result = $avg_query->get_result()->fetch_assoc();
            $average = round($avg_result['avg_total'], 2);
        $avg_query->close();

        $check =$conn->prepare("SELECT id FROM group_averages WHERE group_number = ?");
          $check->bind_param("s",$group_number);
  $check->execute();
      $check_result = $check->get_result();
        if($check_result->num_rows> 0)
             {
         $update = $conn->prepare("UPDATE group_averages SET avg_score = ?, updated_at = NOW() WHERE group_number = ?");
            $update->bind_param("ds", $average, $group_number);
         
          $update->execute();
           
          $update->close();
        } 
        else
            
            {
           
          $insert_avg = $conn->prepare("INSERT INTO group_averages (group_number, group_members, project_title, avg_score) VALUES (?, ?, ?, ?)");
           
          $insert_avg->bind_param("sssd", $group_number, $group_members, $project_title, $average);
           
                $insert_avg->execute();
              $insert_avg->close();
        }
        $check->close();

        $message = "<p style='color:green; text-align:center;'>Evaluation submitted successfully! Group average updated to <b>$average</b>.</p>";
    } else {
        $message = "<p style='color:red; text-align:center;'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$eval_result = $conn->query("SELECT * FROM evaluations ORDER BY created_at DESC");
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
    <div class="logout" style="display:flex;   justify-content:end; width:100%; margin-bottom:10px;">
   
    <form method="POST" action="logout.php" >

<button type="submit">Logout</button>

</form>

</div>
   
<?php if($message != "") echo "<div>{$message}</div>"; ?>

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


<tr>

    <td>Articulate requirements</td>

<td>
    <input type="radio" name="articulate" value="developing" required>
</td>
<td>
    <input type="radio" name="articulate" value="accomplished"></td></tr>
<tr><td>Choose appropriate tools and methods</td>
    <td><input type="radio" name="tools" value="developing" required></td>

    <td><input type="radio" name="tools" value="accomplished"></td></tr>

    <tr><td>Give clear and coherent presentation</td>

<td><input type="radio" name="presentation" value="developing" required></td>



                <td><input type="radio" name="presentation" value="accomplished"></td></tr>


<tr><td>Functioned well as a team</td>

            <td><input type="radio" name="teamwork" value="developing" required></td>


<td><input type="radio" name="teamwork" value="accomplished"></td></tr>


</tbody>


</table>

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
if ($eval_result && $eval_result->num_rows > 0) {
    while ($row = $eval_result->fetch_assoc()) {
        $avg_res = $conn->query("SELECT avg_score FROM group_averages WHERE group_number='{$row['group_number']}'");
        $avg_val = ($avg_res && $avg_res->num_rows > 0) ? $avg_res->fetch_assoc()['avg_score'] : '-';
        echo "<tr>
            <td>" . htmlspecialchars($row['group_number']) . "</td>
            <td>" . htmlspecialchars($row['judge_name']) . "</td>
            <td>{$row['total']}</td>
            <td>{$avg_val}</td>
            <td>" . htmlspecialchars($row['comments']) . "</td>
            <td>{$row['created_at']}</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No evaluations yet.</td></tr>";
}
?>
</tbody>


</table>




</div>



</body>
</html>

<?php $conn->close(); ?>
