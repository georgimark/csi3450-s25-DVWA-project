<?php
// students.php
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
                <h1>Students Table</h1>
                <p>Welcome, Admin!<br>
                Here you can list, create, update, or delete student information.</p>
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <select id="action" name="action" required>
                    <option value="none">-- Select action --</option>
                    <option value="list">List</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                </select><br>
                    <label for="fname">Student's first name: </label>
                    <input type="text" id="fname" name="fname"><br>
                    <label for="lname">Student's last name: </label>
                    <input type="text" id="lname" name="lname"><br>
                    <label for="dob">Student's date of birth: </label>
                    <input type="text" id="dob" name="dob" placeholder="yyyy-mm-dd"><br>
                    <label for="enroll_date">Student's enroll date: </label>
                    <input type="text" id="enroll_date" name="enroll_date" placeholder="yyyy-mm-dd"><br>
                    <label for="stu_num">If updating or deleting, enter Student's ID number:</label>
                    <input type="text" id="stu_num" name="stu_num"><br>
                    <input type="submit" value="Submit">
            </form>
        </header>
        </div>
        
<?php
  $action = $_GET["action"] ?? "list";
  $fname = $_GET["fname"];
  $lname = $_GET["lname"];
  $dob = $_GET["dob"];
  $enroll_date = $_GET["enroll_date"];
  $stu_num = $_GET["stu_num"];
  
  if ($action === "list") {
    $sql = "SELECT student_no, first_name, last_name, dob, join_date FROM student ORDER BY last_name, first_name";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<h2>List of Students</h2>";
        echo "<tr><th>Student ID</th><th>First Name</th><th>Last Name</th><th>DOB</th><th>Join Date</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["student_no"] . "</td><td>" . $row["first_name"] . "</td><td>" . $row["last_name"] .
            "</td><td>" . $row["dob"] . "</td><td>" . $row["join_date"] . "</td></tr>";
        }
        echo "</table>";
    }
  } else if ($action === "create") {
     $stmt = $conn->prepare("INSERT INTO student (first_name, last_name, dob, join_date) VALUES (?, ?, ?, ?)");
     $stmt->bind_param("ssss", $fname, $lname, $dob, $enroll_date);

     if (empty($fname) || empty($lname) || empty($dob) || empty($enroll_date)){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "New student created successfully!"; 
     $stmt->close();
  } else if ($action === "update") {
    $stmt = $conn->prepare("UPDATE student SET first_name = (?), last_name = (?), dob = (?), join_date = (?) WHERE student_no = (?)");
    $stmt->bind_param("ssssi", $fname, $lname, $dob, $enroll_date, $stu_num);

     if (empty($fname) || empty($lname) || empty($dob) || empty($enroll_date) || empty($stu_num)){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "Student updated successfully!";
     $stmt->close();
  } else if ($action === "delete") {
    $stmt = $conn->prepare("DELETE FROM student WHERE student_no = (?)");
    $stmt->bind_param("i", $stu_num);

    if (empty($stu_num)) {
        echo "Student number not entered.";
        $stmt->close();
    }

    $stmt->execute();
    echo "Student deleted successfully!";
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
