<script>
  import ProgressBar from "./ProgressBar.svelte";
  import UploadPreview from "./UploadPreview.svelte";
  import DropZone from "./DropZone.svelte";
  import { createEventDispatcher } from "svelte";

  const dispatch = createEventDispatcher();

  const bytes_per_chunk = 1048576; // 1mb chunk sizes.
  let process = false;
  let progressBarMax = 0;
  let progressBarValue = 0;
  let blobURL = "";
  let video = "";
  
  let preview = false;


  // if upload gets canclled by reloading page etc... this marks flags the video
if(process == true) {

  window.onbeforeunload = function(e) {
    e.preventDefault();
    flagFile();
  };
}

  
  // frontend checking of video format (it is also backed up by server side validation)

  const checkFormat = format => {
    const allowedFormats = [
      "video/webm",
      "video/mp4",
      "video/ogv",
      "video/quicktime"
    ];

    if (allowedFormats.includes(format)) {
      return true;
    } else {
      preview = false;
      document.getElementById("file").value = "";
      alert("format not allowed");
    }
  };


  // function on submitting the form
  const onSubmit = e => {
    e.preventDefault();
    sendRequest();
  };


  // function to get dropped file and added it to the from file input
  const droppedFile = e => {
    document.getElementById("file").files = e.detail;
    if (checkFormat(e.detail[0].type)) showPreview();
  };


  // show if file has been dropped or been loaded from the html file input
  const showPreview = () => {
    video = document.getElementById("file").files[0];
    if (checkFormat(video.type)) {
    }
    preview = true;

    blobURL = URL.createObjectURL(video);
  };

  //sends inital request to server

  const sendRequest = () => {

    
    // allows the file uploaing to happen
    process = true;
    let blob = document.getElementById("file").files[0];

    // gets the total size of the file and added to a variavle
    let total_size = blob.size;

    //initializes the counter that makes sure it chunk of data is sent at the right time to prevent corruption of video
    window.upload_counter = 0;
    window.upload_filearray = [];

    let start = 0;
    let end = bytes_per_chunk;

    // a while loop to create each invidual chunk

    while (start < total_size) {
      let chunk = blob.slice(start, end, blob.type);

      window.upload_filearray[window.upload_counter] = chunk;
      window.upload_counter++;

      start = end;
      end = start + bytes_per_chunk;
    }

    progressBarMax = window.upload_filearray.length;
    progressBarValue = window.upload_counter;
    // initiate upload the first time
    window.upload_counter = 0;
    window.filename = blob.name;

    //calling the uploadfile function with correct chunk of data
    uploadFile(window.upload_filearray[window.upload_counter]);
  };

  const uploadCancelled = () => {
    //stop the upload process
    process = false;
  };

  // this function send each chunk of data then waits for the response before allwing next chunk to be snt

  const sendChunk = async chunk => {
    let data = await fetch(
      `http://localhost/videouploader/api.php?m=uploadVideo&filename=${
        window.filename
      }&status=UploadingChunk`,
      {
        method: "POST",
        body: chunk // Coordinate the body type with 'Content-Type'
      }
    )
      .then(res => res.json())
      .then(json => {
        return json;
      });

    return data;
  };

  // flag file as canclled
  const flagFile = async () => {
    await fetch(
      `http://localhost/videouploader/api.php?m=uploadVideo&status=cancelUpload&filename=${
        window.filename
      }`
    ).then(data => {
      preview = false;
      document.getElementById("file").value = "";
    });
  };


// this function recivies the chunks append the data to a form and calls the send chunk function if the function returns false it calls upload failed function
  const uploadFile = chunk => {
    if (process === true) {
      var fd = new FormData();
      fd.append("fileToUpload", chunk);

      sendChunk(fd)
        .then(data => {
          window.upload_counter++;
          uploadComplete();
        })
        .catch(e => uploadFailed());
    } else {
      flagFile();
    }
  };

  // after all data is uploaded this function is called to tell the server to save that file to the database
  const saveToDB = async () => {
    let res = await fetch(
      `http://localhost/videouploader/api.php?m=uploadVideo&filename=${
        window.filename
      }&status=saveToDB&size=${video.size}&type=${video.type}`
    );
    let data = await res.json();
    return data;
  };

  // thise function checks if there is still data to send if the array of data is bigger then the counter it will call the uploadfile function otherwise it saves to db
  const uploadComplete = () => {
    if (window.upload_filearray.length > window.upload_counter) {
      uploadFile(window.upload_filearray[window.upload_counter]);
      updateProgress();
    } else {
      saveToDB().then(data => {
        (video = {
          id: data.id,
          Name: data.name,
          Size: data.size,
          Format: data.type
        }),
          dispatch("addvideo", video);
        preview = false;
        document.getElementById("file").value = "";
        progressBarValue = progressBarMax;
      });
    }
  };
// updates the progress bar based on the upload counter var
  const updateProgress = () => {
    progressBarValue = window.upload_counter;
  };

  // allows you recall the upload function if the server sends a bad reponse holding the place of the previous upload to make sure the data is not curropted

  const uploadFailed = () => {
    if (
      confirm(
        "There was an error attempting to upload the file. Would you like to retry"
      )
    ) {
      uploadComplete();
    }
  };
</script>

<DropZone on:droppedfile={droppedFile} />

<form class="grid-3" on:submit={onSubmit} enctype="multipart/form-data">
  <div class="form-group">
    <input
      type="file"
      name="fileToUpload"
      id="file"
      class="form-control"
      on:change={showPreview} />
    {#if preview}
      <input type="submit" id="submit" class="btn btn-success" value="Upload" />
    {/if}
  </div>
</form>
{#if preview}
  <div>
    <UploadPreview src={blobURL} />

    <p>Name:{video.name}</p>
    <p>Type:{video.type}</p>
    <p>Size:{app.formatBytes(video.size)}</p>
    <p>Last Edited:{video.lastModified}</p>
  </div>
  <div>
    <ProgressBar
      max={progressBarMax}
      currentValue={progressBarValue}
      on:uploadcancelled={uploadCancelled} />
  </div>
{/if}
