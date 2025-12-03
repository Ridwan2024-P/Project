<?php
session_start();
session_regenerate_id(true);
//$_SESSION = [];

if (!isset($_SESSION['user_id']) || $_SESSION['type'] !== 'judge') {
    header("Location: index.php");
    exit();
}
///$_SESSION['judge_email'];

require_once "class/Database.php";
require_once "class/JudgeEvaluation.php";

$db = new Database();
$eval = new JudgeEvaluation($db);

$message = "";
$judgeEmail = '';

if (isset($_SESSION['user_id'])) {
    // adjust this part based on how your Database class exposes the connection
    $conn = $db->getConnection(); // or $db->conn

    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($judgeEmail);
    $stmt->fetch();
    $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] === "POST")
    
    
    
    {
    $data = [
        'group_members' => $_POST['group_members'] ?? '',
        'group_number' => $_POST['group_number'] ?? '',
        'project_title' => $_POST['project_title'] ?? '',
        'judge_name' => $_SESSION['judge_name'],
        'articulate' => ($_POST['articulate_dev'] !== "" ? $_POST['articulate_dev'] : ($_POST['articulate_acc'] ?? 0)),
        'tools' => ($_POST['tools_dev'] !== "" ? $_POST['tools_dev'] : ($_POST['tools_acc'] ?? 0)),
        'presentation' => ($_POST['presentation_dev'] !== "" ? $_POST['presentation_dev'] : ($_POST['presentation_acc'] ?? 0)),
        'teamwork' => ($_POST['teamwork_dev'] !== "" ? $_POST['teamwork_dev'] : ($_POST['teamwork_acc'] ?? 0)),
        'comments' => $_POST['comments'] ?? '',
    ];



    $result = $eval->submitOrUpdateEvaluation($data);

    if ($result['success']) {
        $message = "<p style='color:green;text-align:center;'>Evaluation saved successfully! 
                    Updated average: <b>{$result['average']}</b></p>";
    } else {
        $message = "<p style='color:red;text-align:center;'>Error: {$result['error']}</p>";
    }
}


$edit_eval = null;
$groupInfo = null;
$group_number = "";
if (isset($_GET['group'])) {
    $group_number = $_GET['group'];
    $edit_eval = $eval->getEvaluation($group_number, $_SESSION['judge_name']);
    $groupInfo = $eval->getGroupInfo($group_number);
}




$eval_result = $eval->getAllEvaluations();



?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Judge Evaluation Form</title>
<link rel="stylesheet" href="judge.css">
<style>
table {
    border:1px solid #000;
    width:95%;
    margin:auto;
    text-align:center;
    border-collapse:collapse;
    background:#fff;
}



th,td {
    padding:8px;
    border:1px solid #000;
}




.btn {
    padding:6px 12px;
    background:#007BFF;
    color:#fff;
    text-decoration:none;
    border-radius:4px;
}
.btn:hover {
    background:#0056b3;
}
</style>
</head>
<body>

<div id="judge">
    <!--
    <div class="logout" style="display:flex; justify-content:end; margin-bottom:10px;">
        <form method="POST" action="logout.php">
            <button type="submit">Logout</button>
              <button><strong><?php echo htmlspecialchars($_SESSION['judge_name']); ?></strong></button>
        </form>
      
    </div>
	-->
            <div style="display:flex; justify-content:flex-end; padding:16px 20px; box-sizing:border-box;">
            <div style="
                display:flex;
                align-items:center;
                gap:10px;
                background:#f5f7fb;
                padding:8px 14px;
                border-radius:999px;
                box-shadow:0 2px 6px rgba(15,23,42,0.12);
                font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                font-size:14px;
                color:#0f172a;
            ">
                <span style="font-weight:600;">
                    <?php echo htmlspecialchars($judgeEmail); ?>
                </span>

                <form method="POST" action="logout.php" style="margin:0; display:inline-block;">
                    <button type="submit" style="
                        border:none;
                        padding:6px 14px;
                        border-radius:999px;
                        background:#0b3b73;
                        color:#ffffff;
                        font-size:13px;
                        font-weight:600;
                        cursor:pointer;
                        letter-spacing:0.3px;
                    ">
                        Logout
                    </button>
                </form>
            </div>
        </div>

    <?php if ($message != "") echo "<div>{$message}</div>"; ?>

    <h2 style="text-align:center;color:#00074f;font-size:35px;">Computer Science Project</h2>

    
    <form method="POST">

        <label>Group Members:</label>
        <input type="text" name="group_members"
               value="<?php echo htmlspecialchars($edit_eval['group_members'] ?? $groupInfo['group_members'] ?? ''); ?>" required>

        <label>Group Number:</label>
        <input type="text" name="group_number"
               value="<?php echo htmlspecialchars($edit_eval['group_number'] ?? $group_number ?? ''); ?>" required>

        <label>Project Title:</label>
        <input type="text" name="project_title"
               value="<?php echo htmlspecialchars($edit_eval['project_title'] ?? $groupInfo['project_title'] ?? ''); ?>" required>

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

foreach ($criteria as $i => $c) {
    $name = $names[$i];
    $dev_id = $name . "_dev";
    $acc_id = $name . "_acc";

    $prefill = $edit_eval[$name] ?? ''; 

    echo "
    <tr>
        <td>{$c}</td>
        <td>
            <input type='radio' name='{$name}_select' onclick=\"
                document.getElementById('{$dev_id}').style.display='block';
                document.getElementById('{$acc_id}').style.display='none';\">
            <input type='number' name='{$name}_dev' id='{$dev_id}'
                   min='0' max='10'
                   style='display:none; width:90%; margin-top:8px;'
                   value='".(($prefill >=0 && $prefill <=10)? htmlspecialchars($prefill):"")."'>
        </td>
        <td>
            <input type='radio' name='{$name}_select' onclick=\"
                document.getElementById('{$acc_id}').style.display='block';
                document.getElementById('{$dev_id}').style.display='none';\">
            <input type='number' name='{$name}_acc' id='{$acc_id}'
                   min='11' max='15'
                   style='display:none; width:90%; margin-top:8px;'
                   value='".(($prefill >=11 && $prefill <=15)? htmlspecialchars($prefill):"")."'>
        </td>
    </tr>";
}
?>
        </tbody>
        </table>

        <label>Judge Name:</label>
        <input type="text" name="judge_name" value="<?php echo htmlspecialchars($_SESSION['judge_name']); ?>" readonly>

        <label>Comments:</label>
        <textarea name="comments"><?php echo htmlspecialchars($edit_eval['comments'] ?? ''); ?></textarea>

        <button type="submit"><?php echo $edit_eval ? "Update Evaluation" : "Submit Grades"; ?></button>
    </form>

    <hr>
</div>

<h3 style="text-align:center;color:#00074f;font-size:30px;">Submitted Evaluations</h3>
    <h2 style="text-align:center;color:#00074f;font-size:30px;">Grade the group's if not graded</h2>
 <table style="gap:5px; width:64.9%; text-align:center; background:#f9f9f9; border-collapse: collapse; margin:0 0px 0px 197px;">
    <thead>
      <tr >
       
        <th>Judge One</th>
        <th>Judge Two</th>
        <th>Judge Three</th>  
        <th>Judge Four</th>
      
      </tr>
    </thead>
</table>
<table style="margin-bottom:50px;">
  <thead>
    <tr>
      <th>Group</th>
      <th>Judge One</th>
      <th>Total</th>
      <th>Comments</th>
      <th>Judge Two</th>
      <th>Total</th>
      <th>Comments</th>
      <th>Judge Three</th>
      <th>Total</th>
      <th>Comments</th>
      <th>Judge Four</th>
      <th>Total</th>
      <th>Comments</th>
      <!-- <th>Average</th>
      <th>Average Grades</th> -->
      <th>Submitted At</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
<?php

$grouped = [];
if ($eval_result && $eval_result->num_rows > 0) {
    while ($row = $eval_result->fetch_assoc()) {
        $grouped[$row['group_number']][] = $row;
    }
}

foreach ($grouped as $group_number => $evaluations) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($group_number) . "</td>";

    $judge_count = 0;
    $total_sum = 0;

    foreach ($evaluations as $row) {
    
        if ($judge_count >= 4) break; 
        
        echo "<td>" . htmlspecialchars($row['judge_name']) . "</td>";
        //echo "<td>" . htmlspecialchars($row['total']) . "</td>";
        echo "<td>**</td>";
        echo "<td>" . htmlspecialchars($row['comments']) . "</td>";
        $total_sum += $row['total'];
        $judge_count++;
    }

    for ($i = $judge_count; $i < 4; $i++) {
        echo "<td>-</td><td>-</td><td>-</td>";
    }

    /*
    $avg_val = $judge_count ? round($total_sum / $judge_count, 2) : '-';

    if ($avg_val === '-') {
        $avg_grade = '-';
    } elseif ($avg_val >= 55.8) {
        $avg_grade = 'A';
    } elseif ($avg_val >= 54) {
        $avg_grade = 'A-';
    } elseif ($avg_val >= 52.2) {
        $avg_grade = 'B+';
    } elseif ($avg_val >= 49.8) {
        $avg_grade = 'B';
 } elseif ($avg_val >= 48) {
        $avg_grade = 'B-';
}
        elseif ($avg_val >= 46.2) {
        $avg_grade = 'C+';
}
        elseif ($avg_val >= 43.8) {
        $avg_grade = 'C';
}
        elseif ($avg_val >= 42) {
        $avg_grade = 'C-';
}  
        elseif ($avg_val >= 40.2) {
        $avg_grade = 'D+';
}
        elseif ($avg_val >= 37.8) {
        $avg_grade = 'D';}
        elseif ($avg_val >= 36) {
        $avg_grade = 'D-';

        else {
        $avg_grade = 'f';
    }

    echo "<td>{$avg_val}</td>";
    echo "<td>{$avg_grade}</td>";

	*/
    $submitted_at = !empty($evaluations) ? end($evaluations)['created_at'] : '-';
    echo "<td>{$submitted_at}</td>";

    $alreadyMarked = false;
    foreach ($evaluations as $evalEntry) {
        if ($evalEntry['judge_name'] === $_SESSION['judge_name']) {
            $alreadyMarked = true;
            break;
        }
    }

   
    if (!$alreadyMarked && $judge_count < 4) { 
        echo "<td><a href='judge.php?group={$group_number}' class='btn'>Grade</a></td>";
    } else {
        echo "<td>-</td>";
    }

    echo "</tr>";
}

?>

  </tbody>
</table>

</body>
</html>
