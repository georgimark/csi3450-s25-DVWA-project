<?php
require __DIR__."/db.php";
require __DIR__."/util.php";
$action = $_GET["action"] ?? "list";
if ($action === "list") {
  $rows = q($pdo, "SELECT instr_num, stu_num, hire_date, instr_status FROM instructors ORDER BY instr_num")->fetchAll();
  echo json_encode($rows); exit;
}
$data = read_json();
if ($action === "create") {
  require_fields($data, ["stu_num","hire_date","instr_status"]);
  q($pdo, "INSERT INTO instructors(stu_num,hire_date,instr_status) VALUES(?,?,?)",
    [$data["stu_num"], $data["hire_date"], $data["instr_status"]]);
  ok(["id"=>$pdo->lastInsertId()]);
}
if ($action === "update") {
  require_fields($data, ["instr_num","stu_num","hire_date","instr_status"]);
  q($pdo, "UPDATE instructors SET stu_num=?, hire_date=?, instr_status=? WHERE instr_num=?",
    [$data["stu_num"], $data["hire_date"], $data["instr_status"], $data["instr_num"]]);
  ok();
}
if ($action === "delete") {
  require_fields($data, ["instr_num"]);
  q($pdo, "DELETE FROM instructors WHERE instr_num=?", [$data["instr_num"]]);
  ok();
}
bad("Unknown action");
