<?php

  // Define SQL connection parameters
  $server = "172.31.96.1";
  $user = "agentry2";
  $pass = "";
  $db = "maru";

  // Establish SQL connection
  $con = mysqli_connect($server, $user, $pass, $db);

  // Verify the connection
  if ($con->connect_error){
    die("Error to create connection: " . $con->connect_error);
  }

  // Prepare the SQL statement
  $stmt = $con->prepare("INSERT INTO student (FirstName, LastName, DOB, JoinDate) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $fname, $lname, $dob, $joindate);

  $stu_no = $_POST['studentnum'];
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $dob = $_POST['dob'];
  $joindate = $_POST['joindate'];
  $user_type = $_POST['registertype'];
  $hire_date = $_POST['hiredate'];
  $instructor_status = $_POST['instructorstatus'];
  
  $stmt->execute();
  $stmt->close();

  // Checks if student is also an instructor
  if ($user_type === "instructor") {
    echo $instructor_status;
    $status = ($_POST['instructorstatus'] === "compensated") ? 'Compensated' : 'Volunteer';
    echo "$status";
      $stmt = $con->prepare("INSERT INTO instructor (StudentNo, FirstName, LastName, DOB, JoinDate, InstructorStartDate, InstructorStatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssssss", $stu_no, $fname, $lname, $dob, $joindate, $hire_date, $status);

      $stmt->execute();
      $stmt->close();
  }


  // Closes the connection
  $con->close();
?>