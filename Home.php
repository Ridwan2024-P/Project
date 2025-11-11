<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "login_system";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

// Fetch all scores
$sql = "SELECT * FROM scores";
$result = $conn->query($sql);

// Calculate group average
$avg_sql = "SELECT group_number, AVG(total_score) as avg_score FROM scores GROUP BY group_number";
$avg_result = $conn->query($avg_sql);
$averages = [];
while ($row = $avg_result->fetch_assoc()) {
  $averages[$row['group_number']] = round($row['avg_score'], 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Scores</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }

    .header {
      background-color: #002b5c;
      color: white;
      padding: 20px 0;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .container {
      width: 90%;
      max-width: 900px;
      margin: 30px auto;
      background-color: white;
      padding: 25px 35px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    h3 {
      color: #002b5c;
      text-align: center;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: center;
      font-size: 14px;
    }

    th {
      background-color: #e5ecf5;
      font-weight: 600;
    }

    tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    tbody tr:hover {
      background-color: #f1f7ff;
    }

    .summary {
      margin-top: 20px;
      text-align: right;
      font-weight: bold;
      font-size: 16px;
      color: #002b5c;
    }

    .footer {
      text-align: center;
      padding: 15px 0;
      margin-top: 30px;
      background-color: #f0f0f0;
      color: #333;
      font-size: 14px;
      border-top: 1px solid #ddd;
    }

    @media screen and (max-width: 600px) {
      th, td {
        font-size: 12px;
        padding: 8px;
      }
    }
  </style>
</head>
<body>

  <header class="header">
    <h2>Project Scores</h2>
  </header>

  <main class="container">
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
        <?php
        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['judge_name']}</td>
                    <td>{$row['group_number']}</td>
                    <td>{$row['total_score']}</td>
                    <td>{$row['comments']}</td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='4'>No scores submitted yet.</td></tr>";
        }
        ?>
      </tbody>
    </table>

    <div class="summary">
      <?php
      foreach ($averages as $group => $avg) {
        echo "<p><strong>Group $group Average:</strong> $avg</p>";
      }
      ?>
    </div>
  </main>

  <footer class="footer">
    <p>Project Scores © 2025</p>
  </footer>

</body>
</html>
<?php $conn->close(); ?>
