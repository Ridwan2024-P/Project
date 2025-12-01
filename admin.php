<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once "class/Database.php";
require_once "class/Admin.php";
require_once "class/Group.php";
require_once "class/Evaluation.php";

$db    = new Database();
$user  = new User($db);
$group = new Group($db);
$eval  = new Evaluation($db);
$message = "";


if(isset($_POST['add_judge'])) {
    $currentJudges = $user->getJudges();
    if($currentJudges && $currentJudges->num_rows >= 4) {
        $message = "<script>alert('Cannot add more than 4 judges!');</script>";
    } else {
        $message = $user->addJudge(trim($_POST['judgeName']), trim($_POST['judgeEmail']), $_POST['judgePassword']);
    }
}

if(isset($_POST['update_judge'])) {
    $message = $user->updateJudge($_POST['judge_id'], trim($_POST['judgeName']), trim($_POST['judgeEmail']), trim($_POST['judgePassword']));
}

if(isset($_POST['update_group'])) {
    $message = $group->updateGroup($_POST['group_id'], $_POST['group_number'], $_POST['group_members'], $_POST['project_title'], $_POST['avg_score']);
}

if(isset($_POST['update_eval'])) {
    $message = $eval->updateEval($_POST['eval_id'], $_POST['total'], $_POST['comments']);
}

if(isset($_POST['delete_judge'])) {
    $judge_id = $_POST['delete_judge_id'];
    $stmt = $db->getConnection()->prepare("DELETE FROM users WHERE id=? AND type='judge'");
    $stmt->bind_param("i", $judge_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php");
    exit();
}


$judges = $user->getJudges();
$groups = $group->getAllGroups();
$evals  = $eval->getAllEvals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="admin.css">
<title>Admin Dashboard</title>
</head>
<body>
<?php echo $message; ?>

<div class="admin">

  <div class="logout">
    <form method="POST" action="logout.php" style="display:inline;">
      <button type="submit">Logout</button>
    </form>
  </div>

  <h3>Add New Judge</h3>
  <form method="POST">
    <input type="text" name="judgeName" placeholder="Judge Name" required>
    <input type="email" name="judgeEmail" placeholder="Judge Email" required>
    <input type="password" name="judgePassword" placeholder="Judge Password" required>
    <button type="submit" name="add_judge">Add Judge</button>
  </form>

  <h3>Manage Judges</h3>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Password</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if($judges && $judges->num_rows>0) {
        while($row = $judges->fetch_assoc()) {
          echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['email']}</td>
            <td>********</td>
            <td>
              <button class='edit-btn' onclick='editJudge(".json_encode($row).")'>Edit</button>
              <form method=\"POST\" style=\"display:inline;\" onsubmit=\"return confirm('Are you sure you want to delete this judge?');\">
                <input type='hidden' name='delete_judge_id' value='{$row['id']}'>
                <button type='submit' name='delete_judge'>Delete</button>
              </form>
            </td>
          </tr>";
        }
      }
      ?>
    </tbody>
  </table>

  <div id="editJudgeForm" class="hidden-form" style="display:none;">
    <h3>Edit Judge</h3>
    <form method="POST">
      <input type="hidden" name="judge_id" id="judge_id">
      <input type="text" name="judgeName" id="judge_name" required>
      <input type="email" name="judgeEmail" id="judge_email" required>
      <input type="password" name="judgePassword" id="judge_password" placeholder="New Password (optional)">
      <button type="submit" name="update_judge">Update Judge</button>
    </form>
  </div>

  <hr>

  <h3>Group Average Scores</h3>
  <table>
    <thead>
      <tr>
        <th>Group Number</th>
        <th>Members</th>
        <th>Title</th>
        <th>Average</th>
        <th>Average Grades</th>
        <th>Updated</th>
       
      </tr>
    </thead>
    <tbody>
      <?php
      
      $grouped = [];
      if($evals && $evals->num_rows > 0) {
       
        $evalRows = [];
        while($r = $evals->fetch_assoc()) { $evalRows[] = $r; }
       
        $evals = new ArrayIterator($evalRows);

        foreach ($evalRows as $row) {
          $grouped[$row['group_number']][] = $row;
        }
      }

      foreach ($grouped as $group_number => $evaluations) {
    echo "<tr>";
    $first = $evaluations[0];
    echo "<td>{$group_number}</td>";
    echo "<td>".htmlspecialchars($first['group_members'])."</td>";
    echo "<td>".htmlspecialchars($first['project_title'])."</td>";

    $judge_count = 0;
    $total_sum = 0;
    $updated_at = "";

    foreach ($evaluations as $e) {
        $total_sum += (float)$e['total'];
        $judge_count++;
        $updated_at = $e['updated_at'] ?: $e['created_at'];
    }

    $avg = $judge_count ? round($total_sum / $judge_count, 2) : '-';

  
    if ($avg === '-') {
        $grade = '-';
    } elseif ($avg >= 45) {
        $grade = 'A';
    } elseif ($avg >= 35) {
        $grade = 'B';
    } elseif ($avg >= 25) {
        $grade = 'C';
    } else {
        $grade = 'D';
    }

    echo "<td>{$avg}</td>";
    echo "<td>{$grade}</td>";
    echo "<td>{$updated_at}</td>";
   
    echo "</tr>";
}


      ?>
    </tbody>
  </table>

 <div id="editGroupForm" class="hidden-form" style="display:none;">
  <h3>Edit Group Score</h3>
  <form method="POST">
    <input type="hidden" name="group_id" id="group_id">
    
    <label>Group Number:</label>
    <input type="text" name="group_number" id="group_number" required>
    
    <label>Members:</label>
    <input type="text" name="group_members" id="group_members" required>
    
    <label>Project Title:</label>
    <input type="text" name="project_title" id="project_title" required>
    
    <label>Average Score:</label>
    <input type="number" step="0.01" name="avg_score" id="avg_score" required>
    
    <button type="submit" name="update_group">Update Group</button>
  </form>
</div>


  <hr>

  <div id="editEvalForm" class="hidden-form" style="display:none;">
    <h3>Edit Judge Evaluation</h3>
    <form method="POST">
      <input type="hidden" name="eval_id" id="eval_id">
      <label>Total:</label>
      <input type="number" step="0.01" name="total" id="eval_total" required>
      <label>Comments:</label>
      <textarea name="comments" id="eval_comments" rows="3" placeholder="Comments"></textarea><br>
      <button type="submit" name="update_eval">Update Evaluation</button>
    </form>
  </div>
</div>
<div style="width:100%; max-width:1500px; height:450px; background:#fff; align:center; margin:0 auto; padding-top:20px;border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1);">
  <h3 style="text-align:center;">All Judge Evaluations</h3>
  <table style="gap:5px; width:85%; text-align:center; background:#f9f9f9; border-collapse: collapse; margin:0 0 0px 86px;">
    <thead>
      <tr >
       
        <th>Judge One</th>
        <th>Judge Two</th>
        <th>Judge Three</th>  
        <th>Judge Four</th>
      
      </tr>
    </thead>
</table>
    
  <table style="margin:0 auto 50px auto; width:80%; text-align:center; background:#f9f9f9;">
    <thead>
      <tr>
        <th>Group</th>
        <th>Judge One</th>
        <th>Total</th>
        <th>Comments</th>
        <th>Action</th>
        <th>Judge Two</th>
        <th>Total</th>
        <th>Comments</th>
        <th>Action</th>
        <th>Judge Three</th>
        <th>Total</th>
        <th>Comments</th>
        <th>Action</th>
        <th>Judge Four</th>
        <th>Total</th>
        <th>Comments</th>
        <th>Action</th>
        <th>Average</th>
        <th>Submitted At</th>
      </tr>
    </thead>
    <tbody>
      <?php
    
      $grouped2 = [];
      foreach ($evalRows ?? [] as $row) {
        $grouped2[$row['group_number']][] = $row;
      }

      foreach ($grouped2 as $group_number => $evaluations) {
        echo "<tr>";
        echo "<td>{$group_number}</td>";

        $judge_count = 0;
        $total_sum = 0;
        $submitted_at = "";

        foreach ($evaluations as $e) {
          echo "<td>".htmlspecialchars($e['judge_name'])."</td>";
          echo "<td>".htmlspecialchars($e['total'])."</td>";
          echo "<td>".htmlspecialchars($e['comments'])."</td>";
          echo "<td><button class='edit-btn' onclick='editEval(".json_encode($e).")'>Edit</button></td>";

          $total_sum += (float)$e['total'];
          $judge_count++;
          $submitted_at = $e['created_at'];
        }

        for ($i = $judge_count; $i < 4; $i++) {
          echo "<td>-</td><td>-</td><td>-</td><td>-</td>";
        }

        $avg = $judge_count ? round($total_sum / $judge_count, 2) : '-';
        echo "<td>{$avg}</td>";
        echo "<td>{$submitted_at}</td>";
        echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<script>
function editJudge(data){
  document.getElementById('editJudgeForm').style.display='block';
  document.getElementById('judge_id').value = data.id;
  document.getElementById('judge_name').value = data.name;
  document.getElementById('judge_email').value = data.email;
  document.getElementById('judge_password').value = '';
  window.scrollTo({ top: document.getElementById('editJudgeForm').offsetTop, behavior: 'smooth' });
}

function editGroup(data) {
  document.getElementById('editGroupForm').style.display = 'block';
  document.getElementById('group_id').value = data.id;
  document.getElementById('group_number').value = data.group_number;
  document.getElementById('group_members').value = data.group_members;
  document.getElementById('project_title').value = data.project_title;
  document.getElementById('avg_score').value = data.avg_score;
  window.scrollTo({ top: document.getElementById('editGroupForm').offsetTop, behavior: 'smooth' });
}


function editEval(data){
  document.getElementById('editEvalForm').style.display='block';
  document.getElementById('eval_id').value = data.id;
  document.getElementById('eval_total').value = data.total;
  document.getElementById('eval_comments').value = data.comments;
  window.scrollTo({ top: document.getElementById('editEvalForm').offsetTop, behavior: 'smooth' });
}
</script>

</body>
</html>
