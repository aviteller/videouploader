<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');


$target_path = "uploads/";
$tmp_name = $_FILES['fileToUpload']['tmp_name'];
$size = $_FILES['fileToUpload']['size'];
$name = $_FILES['fileToUpload']['name'];
$type = $_FILES['fileToUpload']['type'];
$filename = $_GET['filename'];

$target_file = $target_path . basename($name);

$allowed_extensions = array("webm", "mp4", "ogv");
$file_size_max = 2147483648;
$pattern = implode("|", $allowed_extensions);


if (!empty($name)) {    //here is what I changed - as you can see above, I used implode for the array
  // and I am using it in the preg_match. You pro can do the same with file_type,
  // but I will leave that up to you

  if ($_FILES['fileToUpload']['error'] > 0) {
    http_response_code(403);
    exit;
  } else {
    $com = fopen("uploads/" . $filename, "ab");
    error_log($target_path);

    // Open temp file
    $out = fopen($target_file, "wb");

    if ($out) {
      // Read binary input stream and append it to temp file
      $in = fopen($tmp_name, "rb");
      if ($in) {
        while ($buff = fread($in, 1048576)) {
          fwrite($out, $buff);
          fwrite($com, $buff);
        }
      }
      fclose($in);
      fclose($out);
    }
    fclose($com);

    echo json_encode("ok");
    exit;
  }
} else {
  echo "Please select a video to upload.";
}
