<?php
// HOW TO RUN:
// Have the server running first:
// $ php -S 127.0.0.1:8000 -t public
//
// Run it like below:
//
// To get all users:
// $ php client/client.php getallusers
// $ php client/client.php getuser
// adjust the prints for the output
//
// To get one user with ID 10:
// # php client/client.php getuser 10
// adjust the prints for the output
//
// To add a user:
// $ php client/client.php adduser '{"firstname": "Linus", "lastname": "Torvalds"}'
//
// To update a user:
// $ php client/client.php updateuser 5 '{"firstname": "Steve", "lastname": "Jobs", "firstparent_id": 3, "secondparent_id": 2}'
//
// To delete a user:
// $ php client/client.php deleteuser 7

if ($_SERVER['argc'] == 1) {
  echo "Not enough options\n";
  showHelp();
  exit(1);
}

$command = $_SERVER['argv'][1];
$ch = curl_init();
switch($command) {
case 'getallusers':
  getUser($ch);
  break;
case 'getuser':
  $id = null;
  if (array_key_exists(2, $_SERVER['argv']))
    $id = $_SERVER['argv'][2];
  getUser($ch, $id);
  break;
case 'adduser':
  $input = null;
  if (array_key_exists(2, $_SERVER['argv'])) {
    $input = $_SERVER['argv'][2];
  } else {
    echo "data not found\n";
    showHelp();
    exit(1);
  }

  addUser($ch, $input);
  break;
case 'updateuser':
  $id = null;
  if (array_key_exists(2, $_SERVER['argv'])) {
    $id = $_SERVER['argv'][2];
  } else {
    echo "id not found\n";
    showHelp();
    exit(1);
  }

  $input = null;
  if (array_key_exists(3, $_SERVER['argv'])) {
    $input = $_SERVER['argv'][3];
  } else {
    echo "data not found\n";
    showHelp();
    exit(1);
  }

  updateUser($ch, $id, $input);
  break;
case 'deleteuser':
  $id = null;
  if (array_key_exists(2, $_SERVER['argv'])) {
    $id = $_SERVER['argv'][2];
  } else {
    echo "id not found\n";
    showHelp();
    exit(1);
  }

  deleteUser($ch, $id);
  break;
}
curl_close($ch);

function getUser($ch, $id=null) {
  if (isset($id))
    echo "Getting user id $id...\n";
  else
    echo "Getting all users...\n";

  $endpoint = "http://127.0.0.1:8000/person/" . $id ?? '';
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = json_decode(curl_exec($ch));
  //print_raw($result); // var_dump of result
  //print_associative_array($result);  // var_dump of associative array
  print_csv($result); // echo of results in CSV format
}

function addUser($ch, $input) {
  $endpoint = "http://127.0.0.1:8000/person/";
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  $result = json_decode(curl_exec($ch));
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "Status code: $status";
  echo "\n";
  echo "ID: $result->id";
  echo "\n";
}

function updateUser($ch, $id, $input) {
  $endpoint = "http://127.0.0.1:8000/person/" . $id;
  $headers = array(
    "Content-Type: application/json",
    "Content-Length: " . strlen($input)
  );
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch));
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "Status code: $status";
  echo "\n";
  echo "Rows: $result->rows";
  echo "\n";
}

function deleteUser($ch, $id) {
  $endpoint = "http://127.0.0.1:8000/person/" . $id;

  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = json_decode(curl_exec($ch));
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "Status code: $status";
  echo "\n";
  echo "Rows: $result->rows";
  echo "\n";
}

function print_raw($data) {
  var_dump($data);
}

function print_associative_array($data) {
  $table = [];
  foreach($data as $value) {
    //var_dump($value);
    $row = [];
    foreach($value as $key => $item) {
      $row[$key] = $item;
    }
    array_push($table, $row);
  }
  var_dump($table);
}

function print_csv($data) {
  echo implode(',', array_keys(get_object_vars($data[0])));
  echo "\n";
  foreach($data as $value) {
    //foreach($value as $item) {
      //echo $item . ',';
    //}
    echo implode(',', array_values(get_object_vars($value)));
    echo "\n";
  }
}

function showHelp() {
  echo $_SERVER['argv'][0] . " command [id] [data]\n";
  echo "  command = getallusers|getuser|adduser|updateuser|deleteuser\n";
}
?>
