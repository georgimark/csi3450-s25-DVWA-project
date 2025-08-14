<?php
// instructors.php
require_once 'db.php'; // Ensure database connection is available.
require 'util.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title will be set on each individual page -->
    <title><?php echo $page_title ?? 'Martial Arts Admin'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container">
        <header>
            <h1>🥋 Martial Arts School Admin</h1>
            <nav class="main-nav">
                <a href="index.php">Dashboard</a>
                <a href="students.php">Students</a>
                <a href="instructors.php">Instructors</a>
                <a href="classes.php">Classes</a>
                <a href="ranks.php">Ranks</a>
                <a href="schedule.php">Schedule</a>
            </nav>
        </header>

        <main>
            <!-- The main content of each page will go here -->
        <div class="container">
            <header>
                <h1>Instructors Table</h1>
                <p>Welcome, Admin!<br>
                Here you can list, create, update, or delete instructor information.</p>
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <select id="action" name="action" required>
                    <option value="none">-- Select action --</option>
                    <option value="list">List</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                </select><br>
                    <label for="hire_date">Instructor's hire date: </label>
                    <input type="text" id="hire_date" name="hire_date" placeholder="yyyy-mm-dd"><br>
                    <label for="instructor_status">Instructor's status (Compensated or Volunteer): </label>
                    <input type="text" id="instructor_status" name="instructor_status" placeholder="Compensated or Volunteer"><br>
                    <label for="stu_num">If updating or deleting, enter Instructor's ID number:</label>
                    <input type="text" id="stu_num" name="stu_num"><br>
                    <input type="submit" value="Submit">
            </form>
        </header>
        </div>

<?php
  $action = $_GET["action"] ?? "list";
  $hire_date = $_GET["hire_date"];
  $instructor_status = $_GET["instructor_status"];
  $stu_num = $_GET["stu_num"];
  
  if ($action === "list") {
    $sql = "SELECT student_no, instructor_start_date, instructor_status FROM instructor ORDER BY student_no ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<h2>List of Instructors</h2>";
        echo "<tr><th>Instructor ID</th><th>Hire Date</th><th>Status</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["student_no"] . "</td><td>" . $row["instructor_start_date"] . "</td><td>" . $row["instructor_status"] . "</td></tr>";
        }
        echo "</table>";
    }
  } else if ($action === "create") {
     $stmt = $conn->prepare("INSERT INTO instructor (student_no, instructor_start_date, instructor_status) VALUES (?, ?, ?)");
     $stmt->bind_param("iss", $stu_num, $hire_date, $instructor_status);

     if (empty($stu_num) || empty($hire_date) || empty($instructor_status)){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "New instructor created successfully!"; 
     $stmt->close();
  } else if ($action === "update") {
    $stmt = $conn->prepare("UPDATE instructor SET instructor_start_date = (?), instructor_status = (?) WHERE student_no = (?)");
    $stmt->bind_param("ssi", $hire_date, $instructor_status, $stu_num);

     if (empty($hire_date) || empty($instructor_status) || empty($stu_num)){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "Instructor updated successfully!";
     $stmt->close();
  } else if ($action === "delete") {
    $stmt = $conn->prepare("DELETE FROM instructor WHERE student_no = (?)");
    $stmt->bind_param("i", $stu_num);

    if (empty($stu_num)) {
        echo "Instructor number not entered.";
        $stmt->close();
    }

    $stmt->execute();
    echo "Instructor deleted successfully!";
    $stmt->close();
  } else {
        bad("Unknown action");
    }
?>

            <footer>
                <p>Front-end for MARU database. Group DVWA</p>
            </footer>
            </body>
        </main>
</html>
<?php
// Close the database connection at the very end of the page load.
if (isset($conn)) {
    mysqli_close($conn);
}
?>