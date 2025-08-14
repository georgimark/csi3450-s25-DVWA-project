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
                <h1>Ranks Table</h1>
                <p>Welcome, Admin!<br>
                Here you can list, create, update, or delete ranks. You can also award ranks to students and list, create,<br>
                update, or delete rank requirements.</p>
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <select id="action" name="action" required>
                    <option value="none">-- Select action --</option>
                    <option value="list">List</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                    <option value="award">Award</option>
                    <option value="list_award">List Awards</option>
                    <option value="list_req">List Requirements</option>
                    <option value="create_req">Create Requirements</option>
                    <option value="change_req">Change Requirements</option>
                    <option value="delete_req">Delete Requirements</option>
                </select><br>
                    <label for="rank_name">Rank name: </label>
                    <input type="text" id="rank_name" name="rank_name"><br>
                    <label for="belt_color">Rank's belt color: </label>
                    <input type="text" id="belt_color" name="belt_color"><br>
                    <label for="new_rank">If updating, enter new rank name: </label>
                    <input type="text" id="new_rank" name="new_rank"><br>
                    <!--<label for="new_belt">If updating, enter new belt color</label>-->
                    <input type="text" id="new_belt" name="new_belt"><br>
                    <input type="submit" value="Submit">

                <div class="container">
                    <header>
                        <h1>Awarding Ranks to Students</h1>
                        <label for="stu_num">Student's ID: </label>
                        <input type="text" id="stu_num" name="stu_num"><br>
                        <label for="award_name">Rank name: </label>
                        <input type="text" id="award_name" name="award_name"><br>
                        <label for="award_date">Date Awarded: </label>
                        <input type="text" id="award_date" name="award_date" placeholder="yyyy-mm-dd"><br>
                        <input type="submit" value="Submit">
                    </header>
                </div>

                <div class="container">
                    <header>
                        <h1>Rank Requirements</h1>
                        <label for="requirement_id">If updating or deleting, enter the Requirement's ID: </label>
                        <input type="text" id="requirement_id" name="requirement_id"><br>
                        <label for="requirement_rank">Rank name: </label>
                        <input type="text" id="requirement_rank" name="requirement_rank"><br>
                        <label for="requirement_description">Requirement Description: </label>
                        <textarea id="requirement_description" name="requirement_description" rows="10" colspan="30"></textarea><br>
                        <input type="submit" value="Submit">
                    </header>
                </div>
            </form>
        </header>
        </div>

<?php
  $action = $_GET["action"] ?? "list";
  $rank_name = $_GET["rank_name"];
  $belt_color = $_GET["belt_color"];
  $new_rank = $_GET["new_rank"];
  $new_belt = $_GET["new_belt"];
  $stu_num = $_GET["stu_num"];
  $award_name = $_GET["award_name"];
  $award_date = $_GET["award_date"];
  $requirement_id = $_GET["requirement_id"];
  $requirement_rank = $_GET["requirement_rank"];
  $requirement_description = $_GET["requirement_description"];
  
  if ($action === "list") {
    $sql = "SELECT rank_name, belt_color FROM belt_rank ORDER BY rank_name ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<h2>List of Ranks</h2>";
        echo "<tr><th>Rank Name</th><th>Belt Color</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["rank_name"] . "</td><td>" . $row["belt_color"] . "</td></tr>";
        }
        echo "</table>";
    }
  } else if ($action === "create") {
     $stmt = $conn->prepare("INSERT INTO belt_rank (rank_name, belt_color) VALUES (?, ?)");
     $stmt->bind_param("ss", $rank_name, $belt_color);

     if (empty($rank_name) || empty($belt_color)){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "New rank created successfully!"; 
     $stmt->close();
  } else if ($action === "update") {
    $stmt = $conn->prepare("UPDATE belt_rank SET rank_name = (?), belt_color = (?) WHERE rank_name = (?)");
    $stmt->bind_param("sss", $new_rank, $belt_color, $rank_name);

     if (empty($rank_name) || empty($belt_color || empty($new_rank))){
        $stmt->close();
        echo "One or more fields left empty.";
     }

     $stmt->execute();
     echo "Rank updated successfully!";
     $stmt->close();
  } else if ($action === "delete") {
    $stmt = $conn->prepare("DELETE FROM belt_rank WHERE rank_name = (?)");
    $stmt->bind_param("s", $rank_name);

    if (empty($rank_name)) {
        echo "Rank name not entered.";
        $stmt->close();
    }

    $stmt->execute();
    echo "Rank deleted successfully!";
    $stmt->close();
  } else if ($action === "award") {
    $stmt = $conn->prepare("INSERT INTO student_rank (student_no, rank_name, date_awarded) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $stu_num, $award_name, $award_date);

    if (empty($stu_num) || empty($award_name) || empty($award_date)) {
        echo "One or more fields left empty.";
    }

    $stmt->execute();
    echo "Rank awarded successfully!";
    $stmt->close();
  } else if ($action === "list_award") {
    $sql = "SELECT student_no, rank_name, date_awarded FROM student_rank ORDER BY rank_name ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<h2>List of Awarded Ranks</h2>";
        echo "<tr><th>Student Id</th><th>Rank Name</th><th>Date Awarded</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["student_no"] . "</td><td>" . $row["rank_name"] . "</td><td>" . $row["date_awarded"] . "</td></tr>";
        }
        echo "</table>";
    }
  } else if ($action === "list_req") {
    $sql = "SELECT requirement_id, rank_name, requirement_description FROM rank_requirement ORDER BY rank_name ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<h2>List of Requirements</h2>";
        echo "<tr><th>Requirement Id</th><th>Rank Name</th><th>Description</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["requirement_id"] . "</td><td>" . $row["rank_name"] . "</td><td>" . $row["requirement_description"] . "</td></tr>";
        }
        echo "</table>";
    }
  } else if ($action === "create_req") {
    $stmt = $conn->prepare("INSERT INTO rank_requirement (rank_name, requirement_description) VALUES (?, ?)");
    $stmt->bind_param("ss", $requirement_rank, $requirement_description);

    if (empty($requirement_rank) || empty($requirement_description)) {
        echo "One or more fields left empty.";
        $stmt->close();
    }

    $stmt->execute();
    echo "Requirement created successfully!";
    $stmt->close();
  } else if ($action === "change_req") {
    $stmt = $conn->prepare("UPDATE rank_requirement SET rank_name = (?), requirement_description = (?) WHERE requirement_id = (?)");
    $stmt->bind_param("ssi", $requirement_rank, $requirement_description, $requirement_id);

    if (empty($requirement_id) || empty($requirement_rank) || empty($requirement_description)) {
        echo "One or more fields left empty.";
        $stmt->close();
    }

    $stmt->execute();
    echo "Requirement changed successfully!";
    $stmt->close();

  } else if ($action === "delete_req") {
    $stmt = $conn->prepare("DELETE FROM rank_requirement WHERE requirement_id = (?)");
    $stmt->bind_param("i", $requirement_id);

    if (empty($requirement_id)) {
        echo "Requirement ID not entered.";
        $stmt->close();
    }

    $stmt->execute();
    echo "Requirement deleted successfully!";
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