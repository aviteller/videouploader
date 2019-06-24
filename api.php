<?php

require 'core/init.php';

//Letting php know which headers to accept

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');


// making an instance of the pdo wrapper class
$db = DB::instance();

//getting the param to work the switch case
$method = $_REQUEST['m'];




switch ($method) {
  case 'uploadVideo':

    


    $filename = $_GET['filename'];
    $status = $_GET['status'];
    $target_path = "uploads/";

    // this part check the status of the upload
    
    if ($status == 'UploadingChunk') {

      //if it reaches here this is where the chunks of data arrive to add to whole file

      $tmp_name = $_FILES['fileToUpload']['tmp_name'];
      $size = $_FILES['fileToUpload']['size'];
      $name = $_FILES['fileToUpload']['name'];
      $type = $_FILES['fileToUpload']['type'];
      $target_file = $target_path . basename($name);
    
      // decalring allowd formats of video
      $allowed_types = ["video/webm","video/mp4","video/ogv","video/quicktime"];

      // checking if there is a file
      if (!empty($name)) {    
        // if file format is in allowed format types
        if (in_array($type, $allowed_types)) {
          // if error send back a http error code
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
          echo json_encode("format not allowed");exit;
        }
      } else {
        echo "Please select a video to upload.";
      }
    } elseif ($status == 'saveToDB') {
      //after file fully uploaded we insert the file into the db and add some more details
      $target_file = $target_path . basename($filename);
      $db = DB::instance();
    
      $db->run("INSERT INTO `videos` SET `Name` = ?, `Format` = ?, `Size` = ?", [$filename,  $_GET['type'], (int)$_GET['size']]);
      $id = $db->lastInsertId();
    
      $newPath = $id . '_' . $filename;
    
      // renameing the file with the id apppened at the begining the prevent duplication and files being overwritten if they have the same file name
      rename($target_file, "uploads/$newPath");
      
      // sending back file data to be added to javascript array in JSON format
      echo json_encode([
        "id" => $id,
        "name" => $filename,
        "size" => (int)$_GET['size'],
        "type" => $_GET['type']
      ]);
      exit;
    } elseif($status == 'cancelUpload') {
      // if file upload gets cancelled file gets flagged with ._canclled append to end of name
      $target_file = $target_path . basename($filename);
      rename($target_file, "uploads/$filename._cancelled");
      echo json_encode(["message"=> "cancelled"]);exit;
    }
    
    break;
  case 'getVideos':
    // gets all the files that have not been flagged delteed from the database
    $videos = $db->run("SELECT * FROM `videos` WHERE Deleted = 0")->fetchAll();

    if ($videos) {
      echo json_encode($videos);
    } else {
      echo json_encode(
        ['message' => 'No videos Found']
      );
    }
    exit;
    break;
  case 'deleteVideo':
    // get the video details of file wanting to delte
    $video = $db->run("SELECT * from videos WHERE id = ?", [$_GET['id']])->fetch();

    // removes file from directory
    unlink("uploads/{$video->id}_{$video->Name}");


    // updates db to mark file as deleted
    $db->run("UPDATE `videos` SET Deleted = 1 WHERE id = ?", [$_GET['id']]);
    echo json_encode("success");
    exit;
    break;

  default:
    # code...
    break;
}
