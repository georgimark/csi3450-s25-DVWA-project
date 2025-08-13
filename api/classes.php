<?php
require __DIR__."/db.php";
require __DIR__."/util.php";
$action = $_GET["action"] ?? "list";
if ($action === "list") {
  $rows = q($pdo, "SELECT class_id, class_day, class_time, class_room, class_level, head_instr_num FROM classes ORDER BY class_day, class_time")->fetchAll();
  echo json_encode($rows); exit;
}
$data = read_json();
if ($action === "create") {
  require_fields($data, ["class_day","class_time","class_room","class_level","head_instr_num"]);
  q($pdo, "INSERT INTO classes(class_day,class_time,class_room,class_level,head_instr_num) VALUES(?,?,?,?,?)",
    [$data["class_day"], $data["class_time"], $data["class_room"], $data["class_level"], $data["head_instr_num"]]);
  ok(["id"=>$pdo->lastInsertId()]);
}
if ($action === "update") {
  require_fields($data, ["class_id","class_day","class_time","class_room","class_level","head_instr_num"]);
  q($pdo, "UPDATE classes SET class_day=?, class_time=?, class_room=?, class_level=?, head_instr_num=? WHERE class_id=?",
    [$data["class_day"], $data["class_time"], $data["class_room"], $data["class_level"], $data["head_instr_num"], $data["class_id"]]);
  ok();
}
if ($action === "delete") {
  require_fields($data, ["class_id"]);
  q($pdo, "DELETE FROM classes WHERE class_id=?", [$data["class_id"]]);
  ok();
}
bad("Unknown action");
