<?php
require __DIR__."/db.php";
require __DIR__."/util.php";
$action = $_GET["action"] ?? "list";
if ($action === "list") {
  $rows = q($pdo, "SELECT stu_num, fname, lname, dob, enroll_date, current_rank FROM students ORDER BY lname, fname")->fetchAll();
  echo json_encode($rows); exit;
}
$data = read_json();
if ($action === "create") {
  require_fields($data, ["fname","lname","dob","enroll_date"]);
  q($pdo, "INSERT INTO students(fname,lname,dob,enroll_date,current_rank) VALUES(?,?,?,?,?)",
    [$data["fname"], $data["lname"], $data["dob"], $data["enroll_date"], $data["current_rank"] ?? null]);
  ok(["id"=>$pdo->lastInsertId()]);
}
if ($action === "update") {
  require_fields($data, ["stu_num","fname","lname","dob","enroll_date"]);
  q($pdo, "UPDATE students SET fname=?, lname=?, dob=?, enroll_date=?, current_rank=? WHERE stu_num=?",
    [$data["fname"], $data["lname"], $data["dob"], $data["enroll_date"], $data["current_rank"] ?? null, $data["stu_num"]]);
  ok();
}
if ($action === "delete") {
  require_fields($data, ["stu_num"]);
  q($pdo, "DELETE FROM students WHERE stu_num=?", [$data["stu_num"]]);
  ok();
}
bad("Unknown action");
