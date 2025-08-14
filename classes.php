<?php
// classes.php
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
                <h1>Classes Table</h1>
                <p>Welcome, Admin!<br>
                Here you can list, create, update, or delete the information of classes.</p>
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <select id="action" name="action" required>
                    <option value="none">-- Select action --</option>
                    <option value="list">List</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                </select><br>
                    <label for="class_id">If updating or deleting, enter the Class's ID: </label>
                    <input type="text" id="class_id" name="class_id"><br>
                    <label for="class_level">Class' Difficulty (Beginner, Intermediate, Advanced): </label>
                    <input type="text" id="class_level" name="class_level" placeholder="Beginner, Intermediate, Advanced"><br>
                    <label for="class_day">Day the class is on: </label>
                    <input type="text" id="class_day" name="class_day"><br>
                    <label for="start_time">Class' start time: </label>
                    <input type="text" id="start_time" name="start_time" placeholder="hh:mm:ss"><br>
                    <label for="location">Class' location ID: </label>
                    <input type="text" id="location" name="location"><br>
                    <label for="stu_num">Class' assigned instructor's ID number: </label>
                    <input type="text" id="stu_num" name="stu_num"><br>
                    <input type="submit" value="Submit">
            </form>
        </header>
        </div>

<?php
  $action = $_GET["action"] ?? "list";
  $class_id = $_GET["class_id"];
  $class_level = $_GET["class_level"];
  $class_day = $_GET["class_day"];
  $start_time = $_GET["start_time"];
  $location = $_GET["location"];
  $stu_num = $_GET["stu_num"];
  
  if ($action === "list") {
    $sql = "SELECT class_id, level, day_of_week, start_time, location_id, assigned_instructor_no FROM class ORDER BY assigned_instructor_no ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<h2>List of Classes</h2>";
        echo "<tr><th>Class ID</th><th>Difficulty</th><th>Day</th><th>Start Time</th><th>Location ID</th><th>Assigned Instructor's ID</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["class_id"] . "</td><td>" . $row["level"] . "</td><td>" . $row["day_of_week"] . "</td><td>" . $row["start_time"] 
            . "</td><td>" . $row["location_id"] . "</td><td>" . $row["assigned_instructor_no"] . "</td></tr>";
        }
        echo "</table>";
    }
  } else if ($action === "create") {
     $stmt = $conn->prepare("INSERT INTO class (level, day_of_week, start_time, location_id, assigned_instructor_no) VALUES (?, ?, ?, ?, ?)");
     $stmt->bind_param("sssii", $class_level, $class_day, $start_time, $location, $stu_num);

     if (empty($class_level) || empty($class_day) || empty($start_time) || empty($location) || empty($stu_num)){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "New class created successfully!"; 
     $stmt->close();
  } else if ($action === "update") {
    $stmt = $conn->prepare("UPDATE class SET class.level = (?) , day_of_week = (?), start_time = (?), location_id = (?), assigned_instructor_no = (?) WHERE class_id = (?)");
    $stmt->bind_param("sssiii", $class_level, $class_day, $start_time, $location, $stu_num, $class_id);

     if (empty($class_id) || empty($class_level) || empty($class_day) || empty($start_time) || empty($location) || empty($stu_num)){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "Class updated successfully!";
     $stmt->close();
  } else if ($action === "delete") {
    $stmt = $conn->prepare("DELETE FROM class WHERE class_id = (?)");
    $stmt->bind_param("i", $class_id);

    if (empty($class_id)) {
        echo "Class ID not entered.";
        $stmt->close();
    }

    $stmt->execute();
    echo "Class deleted successfully!";
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