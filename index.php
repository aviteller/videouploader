<!DOCTYPE html>
<html>

<head>
  <title>Upload</title>
  <script src="http://code.jquery.com/jquery-2.1.3.min.js"></script>
</head>

<body>
  <form method="POST" action="upload.php" enctype='multipart/form-data'>
    <input type="file" name="fileToUpload" id="file"><br />
    <input type="submit" id="submit">
  </form>

  <button id="cancel">cancel</button>

  <div id="progress-div">

  </div>
</body>

<script>
  $(document).ready(function() {
    $('#submit').on('click', function(e) {
      e.preventDefault();
      sendRequest();
    });


    var bytes_per_chunk = 1048576; // 1mb chunk sizes.


    function sendRequest() {

     


      var blob = document.getElementById('file').files[0];

      var total_size = blob.size;

      window.upload_counter = 0;
      window.upload_filearray = [];


      var start = 0;
      var end = bytes_per_chunk;
      while (start < total_size) {
        var chunk = blob.slice(start, end);
    
        window.upload_filearray[window.upload_counter] = chunk;
        window.upload_counter++;

        start = end;
        end = start + bytes_per_chunk;

      }

      // initiate upload the first time
      window.upload_counter = 0;
      window.filename = blob.name;
      createProgressBar();
      uploadFile(window.upload_filearray[window.upload_counter]);

    }

    document.querySelector('#cancel').addEventListener("click", uploadCanceled);
    var xhr = new XMLHttpRequest();

    function uploadFile(chunk) {

      
      
      var fd = new FormData();
      fd.append('fileToUpload', chunk);
      xhr.open('POST', 'upload.php?filename=' + window.filename);
      xhr.onreadystatechange  = function(e) {
        
        if (this.status == 200) {
          window.upload_counter++;
          uploadComplete(e)
        } else if (this.status == 403) {
          uploadFailed(e)
        } else {
         uploadComplete(e)
        }
      };


      xhr.send(fd);

    }

    const createProgressBar = () => {
      let progBar = document.createElement("progress");
      progBar.setAttribute("max", window.upload_filearray.length);
      progBar.setAttribute("value", window.upload_counter);
      progBar.setAttribute("id", "prog-bar");
      document.querySelector('#progress-div').appendChild(progBar);
    }

    function uploadComplete(evt) {
      // This event is raised when the server send back a response 
      if (evt.target.responseText != "") {
        console.log(evt.target.responseText);
      }

      if (window.upload_filearray.length > window.upload_counter) {
        updateProgress();
        uploadFile(window.upload_filearray[window.upload_counter]);
      } else {
        document.querySelector('#progress-div').innerHTML = "Complete"
      }
    }

    function updateProgress(evt) {

      if (window.upload_filearray.length > window.upload_counter) {
        document.querySelector("#prog-bar").value = window.upload_counter;
      } else {
        document.querySelector('#progress-div').innerHTML = "Complete"
      }
    }

    function uploadFailed(evt) {
      if(confirm("There was an error attempting to upload the file. Would you like to retry")){
        uploadComplete(evt)
      };
    }

    function uploadCanceled() {
      xhr.abort();
      xhr = null;
    }


  });
</script>

</html>