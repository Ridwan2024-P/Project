<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$host = "localhost";
$user = "root";
$pass = "";
$db = "login_system";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// ------------------ ADD JUDGE ------------------
if (isset($_POST['add_judge'])) {
    $name = trim($_POST['judgeName']);
    $email = trim($_POST['judgeEmail']);
    $password = $_POST['judgePassword'];

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<script>alert('Judge with this email already exists!');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, type) VALUES (?, ?, ?, 'judge')");
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        $stmt->execute();
        $message = "<script>alert('Judge Added Successfully!');</script>";
        $stmt->close();
    }
    $check->close();
}

// ------------------ UPDATE JUDGE ------------------
if (isset($_POST['update_judge'])) {
    $id = $_POST['judge_id'];
    $name = trim($_POST['judgeName']);
    $email = trim($_POST['judgeEmail']);
    $password = trim($_POST['judgePassword']);

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $hashed_password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $email, $id);
    }
    $stmt->execute();
    $message = "<script>alert('Judge Updated Successfully!');</script>";
    $stmt->close();
}

// ------------------ UPDATE GROUP SCORE ------------------
if (isset($_POST['update_group'])) {
    $id = $_POST['group_id'];
    $number = $_POST['group_number'];
    $members = $_POST['group_members'];
    $title = $_POST['project_title'];
    $avg = $_POST['avg_score'];

    $stmt = $conn->prepare("UPDATE group_averages SET group_number=?, group_members=?, project_title=?, avg_score=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("sssdi", $number, $members, $title, $avg, $id);
    $stmt->execute();
    $message = "<script>alert('Group Score Updated Successfully!');</script>";
    $stmt->close();
}

// ------------------ UPDATE JUDGE EVALUATION ------------------
if (isset($_POST['update_eval'])) {
    $id = $_POST['eval_id'];
    $total = $_POST['total'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("UPDATE evaluations SET total=?, comments=? WHERE id=?");
    $stmt->bind_param("dsi", $total, $comments, $id);
    $stmt->execute();
    $message = "<script>alert('Evaluation Updated Successfully!');</script>";
    $stmt->close();
}

// ------------------ FETCH DATA ------------------
$judges = $conn->query("SELECT * FROM users WHERE type='judge' ORDER BY id DESC");
$averages = $conn->query("SELECT * FROM group_averages ORDER BY avg_score DESC");
$evaluations = $conn->query("SELECT * FROM evaluations ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<style>
body {font-family: Arial; background:#f5f7fa; margin:0; padding:0;}
.header {background:#002b5c; color:white; text-align:center; padding:15px;}
.container {width:95%; max-width:1100px; margin:30px auto; background:white; padding:25px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
table {width:100%; border-collapse:collapse; margin-top:20px;}
th, td {border:1px solid #ccc; padding:10px; text-align:center;}
th {background:#e5ecf5;}
button {background:#002b5c; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;}
button:hover {background:#004b9a;}
.edit-btn {background:orange;}
form input, form textarea {margin:5px 0; padding:8px; width:250px; border-radius:5px; border:1px solid #ccc;}
.logout {text-align:right; margin-bottom:10px;}
.hidden-form {display:none; margin-top:25px;}
</style>
</head>
<body>
<?php echo $message; ?>

<header class="header"><h2>Administrator Dashboard</h2></header>
<main class="container">
<div class="logout">
<form method="POST" action="logout.php" style="display:inline;">
<button type="submit">Logout</button>
</form>
</div>

<!-- ADD NEW JUDGE -->
<h3>Add New Judge</h3>
<form method="POST">
    <input type="text" name="judgeName" placeholder="Judge Name" required>
    <input type="email" name="judgeEmail" placeholder="Judge Email" required>
    <input type="password" name="judgePassword" placeholder="Judge Password" required>
    <button type="submit" name="add_judge">Add Judge</button>
</form>

<!-- JUDGES TABLE -->
<h3>Manage Judges</h3>
<table>
<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Password</th>
<th>Action</th></tr></thead>
<tbody>
<?php
if ($judges && $judges->num_rows > 0) {
    while ($row = $judges->fetch_assoc()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['email']}</td>
            <td>{$row['password']}</td>
            <td><button class='edit-btn' onclick='editJudge(" . json_encode($row) . ")'>Edit</button></td>
        </tr>";
    }
}
?>
</tbody>
</table>

<!-- EDIT JUDGE FORM -->
<div id="editJudgeForm" class="hidden-form">
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

<!-- GROUP AVERAGES -->
<h3>Group Average Scores</h3>
<table>
<thead><tr><th>Group Number</th><th>Members</th><th>Title</th><th>Average</th><th>Updated</th><th>Action</th></tr></thead>
<tbody>
<?php
if ($averages && $averages->num_rows > 0) {
    while ($row = $averages->fetch_assoc()) {
        echo "<tr>
            <td>{$row['group_number']}</td>
            <td>{$row['group_members']}</td>
            <td>{$row['project_title']}</td>
            <td><b>{$row['avg_score']}</b></td>
            <td>{$row['updated_at']}</td>
            <td><button class='edit-btn' onclick='editGroup(" . json_encode($row) . ")'>Edit</button></td>
        </tr>";
    }
}
?>
</tbody>
</table>

<!-- EDIT GROUP FORM -->
<div id="editGroupForm" class="hidden-form">
<h3>Edit Group Score</h3>
<form method="POST">
    <input type="hidden" name="group_id" id="group_id">
    <input type="text" name="group_number" id="group_number" required>
    <input type="text" name="group_members" id="group_members" required>
    <input type="text" name="project_title" id="project_title" required>
    <input type="number" step="0.01" name="avg_score" id="avg_score" required>
    <button type="submit" name="update_group">Update Group</button>
</form>
</div>

<hr>

<!-- ALL EVALUATIONS -->
<h3>All Judge Evaluations</h3>
<table>
<thead><tr><th>Group</th><th>Judge</th><th>Total</th><th>Comments</th><th>Date</th><th>Action</th></tr></thead>
<tbody>
<?php
if ($evaluations && $evaluations->num_rows > 0) {
    while ($row = $evaluations->fetch_assoc()) {
        echo "<tr>
            <td>{$row['group_number']}</td>
            <td>{$row['judge_name']}</td>
            <td>{$row['total']}</td>
            <td>{$row['comments']}</td>
            <td>{$row['created_at']}</td>
            <td><button class='edit-btn' onclick='editEval(" . json_encode($row) . ")'>Edit</button></td>
        </tr>";
    }
}
?>
</tbody>
</table>

<!-- EDIT EVALUATION FORM -->
<div id="editEvalForm" class="hidden-form">
<h3>Edit Judge Evaluation</h3>
<form method="POST">
    <input type="hidden" name="eval_id" id="eval_id">
    <input type="number" step="0.01" name="total" id="eval_total" required>
    <textarea name="comments" id="eval_comments" rows="3" placeholder="Comments"></textarea><br>
    <button type="submit" name="update_eval">Update Evaluation</button>
</form>
</div>

</main>

<footer class="header" style="background:#f0f0f0; color:#333;">
<p>© 2025 Judge Evaluation System</p>
</footer>

<script>
function editJudge(data){
  document.getElementById('editJudgeForm').style.display='block';
  document.getElementById('judge_id').value=data.id;
  document.getElementById('judge_name').value=data.name;
  document.getElementById('judge_email').value=data.email;
  document.getElementById('judge_password').value='';
  window.scrollTo({top:document.getElementById('editJudgeForm').offsetTop,behavior:'smooth'});
}

function editGroup(data){
  document.getElementById('editGroupForm').style.display='block';
  document.getElementById('group_id').value=data.id;
  document.getElementById('group_number').value=data.group_number;
  document.getElementById('group_members').value=data.group_members;
  document.getElementById('project_title').value=data.project_title;
  document.getElementById('avg_score').value=data.avg_score;
  window.scrollTo({top:document.getElementById('editGroupForm').offsetTop,behavior:'smooth'});
}

function editEval(data){
  document.getElementById('editEvalForm').style.display='block';
  document.getElementById('eval_id').value=data.id;
  document.getElementById('eval_total').value=data.total;
  document.getElementById('eval_comments').value=data.comments;
  window.scrollTo({top:document.getElementById('editEvalForm').offsetTop,behavior:'smooth'});
}
</script>

</body>
</html>

<?php $conn->close(); ?>
