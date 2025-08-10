<?php

   //Establish SQL connection parameters
   $server = "172.31.96.1";
   $userName = "agentry2";
   $pass = "";
   $db = "maru";

   //Create connection
   $con=mysqli_connect($server, $userName, $pass, $db);

   //Check connection
   if ($con->connect_error) {
     die("Connection failed: " . $con->connect_error);
   }

   //Prepare SQL statement
   $stmt = $con->prepare("INSERT INTO student (FirstName, LastName, DOB, JoinDate) VALUES (?, ?, ?, ?)");
   $stmt->bind_param("ssss", $fname, $lname, $dob, $enrollment);

   $fname = $_POST['fname'];
   $lname = $_POST['lname'];
   $dob = $_POST['dob'];
   $enrollment = $_POST['enrollment'];

   //Execute statement
   $stmt->execute();

   //Close connection
   $stmt->close();
   $con->close();
?>