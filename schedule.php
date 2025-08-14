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
                <h1>Schedule Table</h1>
                <p>Welcome, Admin!<br>
                Here you can list, create, update, or delete the information of class meetings, teaching assignments, locations, and attendance.</p>
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <select id="action" name="action" required>
                    <option value="none">-- Select action --</option>
                    <option value="list">List</option>
                    <option value="create">Create</option>
                    <option value="update">Update</option>
                    <option value="delete">Delete</option>
                    <option value="list_assignment">List Teaching</option>
                    <option value="create_assignment">Create Teaching</option>
                    <option value="update_assignment">Update Teaching</option>
                    <option value="delete_assignment">Delete Assignment</option>
                    <option value="list_location">List Location</option>
                    <option value="create_location">Create Location</option>
                    <option value="update_location">Update Location</option>
                    <option value="delete_location">Delete Location</option>
                    <option value="list_attendance">List Attendants</option>
                    <option value="create_attendance">Create Attendants</option>
                    <option value="update_attendance">Update Attendants</option>
                    <option value="delete_attendance">Delete Attendants</option>
                </select><br>
                    <label for="meeting_id">Enter Meeting ID: </label>
                    <input type="text" id="meeting_id" name="meeting_id"><br>
                    <label for="class_id">Enter Class ID: </label>
                    <input type="text" id="class_id" name="class_id"><br>
                    <label for="meeting_date">Enter class' meeting date: </label>
                    <input type="text" id="meeting_date" name="meeting_date" placeholder="yyyy-mm-dd"><br>
                    <input type="submit" value="Submit">

                    <div class="container">
                        <header>
                            <h1>Teaching Assignments</h1>
                            <label for="teaching_meeting">Enter Meeting ID: </label>
                            <input type="text" id="teaching_metting" name="teaching_meeting"><br>
                            <label for="stu_num">Enter instructor's ID: </label>
                            <input type="text" id="stu_num" name="stu_num"><br>
                            <label for="instructor_role">Enter instructor's role (Head or Assistant): </label>
                            <input type="text" id="instructor_role" name="instructor_role" placeholder="Head or Assistant"><br>
                            <label for="head_instr_id">Enter head instructor's meeting ID: </label>
                            <input type="text" id="head_instr_id" name="head_instr_id"><br>
                        </header>
                    </div>

                    <div class="container">
                        <header>
                            <h1>Locations</h1>
                            <label for="location_id">Enter location's ID: </label>
                            <input type="text" id="location_id" name="location_id"><br>
                            <label for="room_label">Enter Room Label: </label>
                            <input type="text" id="room_label" name="room_label"><br>
                        </header>
                    </div>

                    <div class="container">
                        <header>
                            <h1>Meeting Attendance</h1>
                            <label for="attend_id">Enter Meeting ID: </label>
                            <input type="text" id="attend_id" name="attend_id"><br>
                            <label for="attendee_id">Enter attendee's ID: </label>
                            <input type="text" id="attendee_id" name="attendee_id"><br>
                        </header>
                    </div>
            </form>
        </header>
        </div>

<?php
  $action = $_GET["action"] ?? "list";
  $meeting_id = $_GET["meeting_id"];
  $class_id = $_GET["class_id"];
  $meeting_date = $_GET["meeting_date"];
  $teaching_meeting = $_GET["teaching_meeting"];
  $stu_num = $_GET["stu_num"];
  $role = $_GET["instructor_role"];
  $head_instr_id = $_GET["head_instr_id"];
  $location_id = $_GET["location_id"];
  $room_label = $_GET["room_label"];
  $attend_id = $_GET["attend_id"];
  $attendee_id = $_GET["attendee_id"];

    if ($action === "list") {
        $sql = "SELECT meeting_id, class_id, meeting_date FROM class_meeting ORDER BY class_id ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<h2>List of Meetings</h2>";
            echo "<tr><th>Meeting ID</th><th>Class ID</th><th>Meeting Date</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["meeting_id"] . "</td><td>" . $row["class_id"] . "</td><td>" . $row["meeting_date"] . "</td></tr>";
            }
            echo "</table>";
        }
    } else if ($action === "create") {
        $stmt = $conn->prepare("INSERT INTO class_meeting (class_id, meeting_date) VALUES (?, ?)");
        $stmt->bind_param("is", $class_id, $meeting_date);

        if (empty($class_id) || empty($meeting_date)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Meeting created successfully!";
        $stmt->close();
    } else if ($action === "update") {
        $stmt = $conn->prepare("UPDATE class_meeting SET class_id = ?, meeting_date = ? WHERE meeting_id = ?");
        $stmt->bind_param("isi", $class_id, $meeting_date, $meeting_id);

        if (empty($class_id) || empty($meeting_date) || empty($meeting_id)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Meeting updated successfully!";
        $stmt->close();
    } else if ($action === "delete") {
        $stmt = $conn->prepare("DELETE FROM class_meeting WHERE meeting_id = ?");
        $stmt->bind_param("i", $meeting_id);

        if (empty($meeting_id)) {
            $stmt->close();
            echo "Meeting ID not entered.";
        }

        $stmt->execute();
        echo "Meeting deleted successfully!";
        $stmt->close();
    } else if ($action === "list_assignment") {
        $sql = "SELECT meeting_id, student_no, role, head_meeting_id FROM teaching_assignment ORDER BY meeting_id ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<h2>List of Assignments</h2>";
            echo "<tr><th>Meeting ID</th><th>Instructor ID</th><th>Role</th><th>Meeting ID</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["meeting_id"] . "</td><td>" . $row["student_no"] . "</td><td>" . $row["role"] . "</td><td>" . $row["head_meeting_id"] . "</td></tr>";
            }
            echo "</table>";
        }
    } else if ($action === "create_assignment") {
        $stmt = $conn->prepare("INSERT INTO teaching_assignment (meeting_id, student_no, role, head_meeting_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $teaching_meeting, $stu_num, $role, $head_instr_id);

        if (empty($teaching_meeting) || empty($stu_num) || empty($role) || empty($head_instr_id)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Assignment created successfully!";
        $stmt->close();
    } else if ($action === "update_assignment") {
        $stmt = $conn->prepare("UPDATE teaching_assignment SET student_no = ?, role = ?, head_meeting_id WHERE meeting_id = ?");
        $stmt->bind_param("isii", $stu_num, $role, $head_instr_id, $teaching_meeting);

        if (empty($teaching_meeting) || empty($stu_num) || empty($role) || empty($he)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Meeting updated successfully!";
        $stmt->close();
    } else if ($action === "delete_assignment") {
        $stmt = $conn->prepare("DELETE FROM teaching_assignment WHERE meeting_id = ?");
        $stmt->bind_param("i", $teaching_meeting);

        if (empty($teaching_meeting)) {
            $stmt->close();
            echo "Meeting ID not entered.";
        }

        $stmt->execute();
        echo "Assignment deleted successfully!";
        $stmt->close();
    } else if ($action === "list_location") {
        $sql = "SELECT location_id, room_label FROM location ORDER BY location_id ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<h2>List of Locations</h2>";
            echo "<tr><th>Location ID</th><th>Room Label</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["location_id"] . "</td><td>" . $row["room_label"] . "</td></tr>";
            }
            echo "</table>";
        }
    } else if ($action === "create_location") {
        $stmt = $conn->prepare("INSERT INTO location (location_id, room_label) VALUES (?, ?)");
        $stmt->bind_param("ii", $location_id, $room_label);

        if (empty($location_id) || empty($room_label)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Location created successfully!";
        $stmt->close();
    } else if ($action === "update_location") {
        $stmt = $conn->prepare("UPDATE location SET room_label = ? WHERE location_id = ?");
        $stmt->bind_param("ii", $room_label, $location_id);

        if (empty($room_label) || empty($location_id)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Location updated successfully!";
        $stmt->close();
    } else if ($action === "delete_location") {
        $stmt = $conn->prepare("DELETE FROM location WHERE location_id = ?");
        $stmt->bind_param("i", $location_id);

        if (empty($location_id)) {
            $stmt->close();
            echo "Location ID not entered.";
        }

        $stmt->execute();
        echo "Location deleted successfully!";
        $stmt->close();
    } else if ($action === "list_attendance") {
        $sql = "SELECT meeting_id, student_no FROM attendance ORDER BY meeting_id ASC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<h2>List of Attendees</h2>";
            echo "<tr><th>Meeting ID</th><th>Attendee ID</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["meeting_id"] . "</td><td>" . $row["student_no"] . "</td></tr>";
            }
            echo "</table>";
        }
    } else if ($action === "create_attendance") {
        $stmt = $conn->prepare("INSERT INTO attendance (meeting_id, student_no) VALUES (?, ?)");
        $stmt->bind_param("ii", $attend_id, $attendee_id);

        if (empty($attend_id) || empty($attendee_id)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Attendance record created successfully!";
        $stmt->close();
    } else if ($action === "update_attendance") {
        $stmt = $conn->prepare("UPDATE attendance SET student_no = ? WHERE meeting_id = ?");
        $stmt->bind_param("ii", $attendee_id, $attend_id);

        if (empty($attendee_id) || empty($attend_id)) {
            $stmt->close();
            echo "One or more fields left empty.";
        }

        $stmt->execute();
        echo "Attendance record updated successfully!";
        $stmt->close();
    } else if ($action === "delete_attendance") {
        $stmt = $conn->prepare("DELETE FROM attendance WHERE meeting_id = ?");
        $stmt->bind_param("i", $meeting_id_id);

        if (empty($meeting_id)) {
            $stmt->close();
            echo "Meeting ID not entered.";
        }

        $stmt->execute();
        echo "Attendance record deleted successfully!";
        $stmt->close();
    } else {
        bad("Unkown action.");
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