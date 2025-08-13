<?php
function read_json() {
  $raw = file_get_contents("php://input");
  if ($raw === false || $raw === "") return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function ok($extra = []) { echo json_encode(array_merge(["ok"=>true], $extra)); exit; }
function bad($msg, $code=400) { http_response_code($code); echo json_encode(["error"=>$msg]); exit; }
function require_fields($data, $fields) {
  foreach ($fields as $f) if (!isset($data[$f]) || $data[$f]==="") bad("Missing field: $f");
}
function q($pdo, $sql, $args=[]) {
  $st = $pdo->prepare($sql); $st->execute($args); return $st;
}
