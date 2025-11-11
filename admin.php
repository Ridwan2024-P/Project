<?php
// ---------- CONFIGURATION ----------
$host = "localhost";
$user = "root";
$pass = "";
$db = "login_system";

// ---------- DATABASE CONNECTION ----------
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Database Connection Failed: " . $conn->connect_error);
}

// ---------- ADD JUDGE ----------
$message = "";
if (isset($_POST['add_judge'])) {
  $name = $_POST['judgeName'];
  $email = $_POST['judgeEmail'];
  $password = $_POST['judgePassword'];

 $sql = "INSERT INTO users (name, email, password, type) VALUES ('$name', '$email', '$password', 'judge')";

  if ($conn->query($sql)) {
    $message = "<script>alert('Judge Added Successfully!');</script>";
  } else {
    $message = "<script>alert('Error Adding Judge: " . $conn->error . "');</script>";
  }
}

// ---------- CREATE PROJECT ----------
if (isset($_POST['create_project'])) {
  $title = $_POST['projectTitle'];
  $groupNumber = $_POST['groupNumber'];
  $groupMembers = $_POST['groupMembers'];

  $sql = "INSERT INTO projects (title, group_number, members) VALUES ('$title', '$groupNumber', '$groupMembers')";
  if ($conn->query($sql)) {
    $message = "<script>alert('Project Created Successfully!');</script>";
  } else {
    $message = "<script>alert('Error Creating Project: " . $conn->error . "');</script>";
  }
}

// ---------- FETCH SCORES ----------
$scores = $conn->query("SELECT * FROM scores");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; }
    .header { background-color: #002b5c; color: white; padding: 15px 0; text-align: center; }
    .container { width: 90%; max-width: 900px; margin: 30px auto; background-color: white;
      padding: 20px 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 10px; }
    h3 { text-align: left; color: #002b5c; margin-bottom: 10px; }
    .form-section { margin-bottom: 40px; }
    input, textarea { width: 100%; padding: 8px; margin: 5px 0 15px;
      border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    button { display: block; width: 100%; background-color: #002b5c; color: white;
      border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-size: 16px; }
    button:hover { background-color: #004b9a; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
    th { background-color: #e5ecf5; }
    .footer { text-align: center; padding: 15px 0; margin-top: 20px;
      background-color: #f0f0f0; color: #333; font-size: 14px; }
  </style>
</head>
<body>
  <?php echo $message; ?>

  <header class="header">
    <h2>Admin Dashboard</h2>
  </header>

  <main class="container">

    <!-- Add Judge -->
    <section class="form-section">
      <h3>Add New Judge</h3>
      <form method="POST">
        <input type="text" name="judgeName" placeholder="Judge Name" required>
        <input type="email" name="judgeEmail" placeholder="Judge Email" required>
        <input type="password" name="judgePassword" placeholder="Judge Password" required>
        <button type="submit" name="add_judge">Add Judge</button>
      </form>
    </section>

    <!-- Create Project -->
    <section class="form-section">
      <h3>Create New Project</h3>
      <form method="POST">
        <input type="text" name="projectTitle" placeholder="Project Title" required>
        <input type="number" name="groupNumber" placeholder="Group Number" required>
        <textarea name="groupMembers" placeholder="Enter group members separated by commas" required></textarea>
        <button type="submit" name="create_project">Create Project</button>
      </form>
    </section>

    <!-- Judges' Scores -->
    <section>
      <h3>Judges' Submitted Scores</h3>
      <table>
        <thead>
          <tr>
            <th>Judge Name</th>
            <th>Group Number</th>
            <th>Total Score</th>
            <th>Comments</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($scores->num_rows > 0): ?>
            <?php while ($row = $scores->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['judge_name']); ?></td>
                <td><?= htmlspecialchars($row['group_number']); ?></td>
                <td><?= htmlspecialchars($row['total_score']); ?></td>
                <td><?= htmlspecialchars($row['comments']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4">No scores submitted yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

  </main>

  <footer class="footer">
    <p>Admin Panel © 2025</p>
  </footer>
</body>
</html>
