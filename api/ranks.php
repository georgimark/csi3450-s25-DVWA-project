<?php
require __DIR__."/db.php";
require __DIR__."/util.php";
$action = $_GET["action"] ?? "list";
if ($action === "list") {
  $rows = q($pdo, "SELECT rank_id, rank_name, rank_belt_color, rank_requirement FROM ranks ORDER BY rank_id")->fetchAll();
  echo json_encode($rows); exit;
}
$data = read_json();
if ($action === "create") {
  require_fields($data, ["rank_name","rank_belt_color","rank_requirement"]);
  q($pdo, "INSERT INTO ranks(rank_name,rank_belt_color,rank_requirement) VALUES(?,?,?)",
    [$data["rank_name"], $data["rank_belt_color"], $data["rank_requirement"]]);
  ok(["id"=>$pdo->lastInsertId()]);
}
if ($action === "update") {
  require_fields($data, ["rank_id","rank_name","rank_belt_color","rank_requirement"]);
  q($pdo, "UPDATE ranks SET rank_name=?, rank_belt_color=?, rank_requirement=? WHERE rank_id=?",
    [$data["rank_name"], $data["rank_belt_color"], $data["rank_requirement"], $data["rank_id"]]);
  ok();
}
if ($action === "delete") {
  require_fields($data, ["rank_id"]);
  q($pdo, "DELETE FROM ranks WHERE rank_id=?", [$data["rank_id"]]);
  ok();
}
bad("Unknown action");
