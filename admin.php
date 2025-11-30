<?php
session_start();
if(!isset($_SESSION['user_id']))
{

    header("Location: index.php");

    exit();
}



require_once "class/Database.php";
require_once "class/Admin.php";
require_once "class/Group.php";
require_once "class/Evaluation.php";
$db=new Database();
$user=new User($db);
$group=new Group($db);
$eval=new Evaluation($db);
$message = "";



if(isset($_POST['add_judge'])) 
    {

    $message = $user->addJudge(trim($_POST['judgeName']), trim($_POST['judgeEmail']), $_POST['judgePassword']);



}

if(isset($_POST['update_judge']))
    
    {

    $message = $user->updateJudge($_POST['judge_id'], trim($_POST['judgeName']), trim($_POST['judgeEmail']), trim($_POST['judgePassword']));



}

if(isset($_POST['update_group']))
    
    {

    $message = $group->updateGroup($_POST['group_id'], $_POST['group_number'], $_POST['group_members'], $_POST['project_title'], $_POST['avg_score']);
}

if(isset($_POST['update_eval'])) 
    
    
    {


        
    $message = $eval->updateEval($_POST['eval_id'], $_POST['total'], $_POST['comments']);
}


$judges=$user->getJudges();
$groups=$group->getAllGroups();
$evals=$eval->getAllEvals();




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
if($judges && $judges->num_rows>0)
     {
    while($row = $judges->fetch_assoc())
        {

        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['email']}</td>
            <td>********</td>
            <td><button class='edit-btn' onclick='editJudge(".json_encode($row).")'>Edit</button></td>
        </tr>";

    }
}


?>

</tbody>

</table>

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

<h3>Group Average Scores</h3>
<table>
<thead>
<tr>

    <th>Group Number</th>
    <th>Members</th>
    <th>Title</th>
    <th>Average</th>
    <th>Updated</th>
    <th>Action</th>

</tr>

</thead>

<tbody>





<?php




if($groups && $groups->num_rows>0)
     {
    while($row=$groups->fetch_assoc())
        {
        echo "<tr>
            <td>{$row['group_number']}</td>
            <td>{$row['group_members']}</td>
            <td>{$row['project_title']}</td>
            <td><b>{$row['avg_score']}</b></td>
            <td>{$row['updated_at']}</td>
            <td><button class='edit-btn' onclick='editGroup(".json_encode($row).")'>Edit</button></td>
        </tr>";
    }
}







?>
</tbody>

</table>

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

<h3>All Judge Evaluations</h3>
<table>
<thead>
<tr><th>Group</th>
<th>Judge</th>
<th>Total</th>
<th>Comments</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
if($evals && $evals->num_rows>0) 
    
    
    {
    while($row=$evals->fetch_assoc())
        
        
        {
        echo "<tr>
            <td>{$row['group_number']}</td>
            <td>{$row['judge_name']}</td>
            <td>{$row['total']}</td>
            <td>{$row['comments']}</td>
            <td>{$row['created_at']}</td>
            <td><button class='edit-btn' onclick='editEval(".json_encode($row).")'>Edit</button></td>
        </tr>";
    }







}





?>
</tbody>
</table>

<div id="editEvalForm" class="hidden-form">
<h3>Edit Judge Evaluation</h3>

<form method="POST">
    <input type="hidden" name="eval_id" id="eval_id">
    <input type="number" step="0.01" name="total" id="eval_total" required>
    <textarea name="comments" id="eval_comments" rows="3" placeholder="Comments"></textarea><br>
    <button type="submit" name="update_eval">Update Evaluation</button>



</form>
</div>

</div>

<script>
function editJudge(data){
document.getElementById('editJudgeForm').style.display='block';
document.getElementById('judge_id').value=data.id;
document.getElementById('judge_name').value=data.name;

document.getElementById('judge_email').value=data.email;
    document.getElementById('judge_password').value='';
    window.scrollTo({top:document.getElementById('editJudgeForm').offsetTop,behavior:'smooth'});




}
function editGroup(data)
{




document.getElementById('editGroupForm').style.display='block';
document.getElementById('group_id').value=data.id;
document.getElementById('group_number').value=data.group_number;
document.getElementById('group_members').value=data.group_members;
document.getElementById('project_title').value=data.project_title;
document.getElementById('avg_score').value=data.avg_score;
window.scrollTo({top:document.getElementById('editGroupForm').offsetTop,behavior:'smooth'});




}




function editEval(data)
{


document.getElementById('editEvalForm').style.display='block';
document.getElementById('eval_id').value=data.id;
document.getElementById('eval_total').value=data.total;
document.getElementById('eval_comments').value=data.comments;
window.scrollTo({top:document.getElementById('editEvalForm').offsetTop,behavior:'smooth'});



}




</script>

</body>
</html>
