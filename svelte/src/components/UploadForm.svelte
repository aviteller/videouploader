<script>
  import ProgressBar from "./ProgressBar.svelte";
  import UploadPreview from "./UploadPreview.svelte";
  import DropZone from "./DropZone.svelte";

  const bytes_per_chunk = 1048576; // 1mb chunk sizes.
  let xhr = new XMLHttpRequest();
  let progressBarMax = 0;
  let progressBarValue = 0;
  let blobURL = "";
  let video = "";
  let preview = false;

  const onSubmit = e => {
    e.preventDefault();
    sendRequest();
  };

  const droppedFile = e => {
    document.getElementById("file").files = e.detail;
    showPreview();
  };

  const showPreview = () => {
    preview = true;
    video = document.getElementById("file").files[0];

    blobURL = URL.createObjectURL(video);
  };

  const sendRequest = () => {
    var blob = document.getElementById("file").files[0];

    var total_size = blob.size;

    window.upload_counter = 0;
    window.upload_filearray = [];

    var start = 0;
    var end = bytes_per_chunk;
    while (start < total_size) {
      var chunk = blob.slice(start, end, blob.type);

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

    uploadFile(window.upload_filearray[window.upload_counter]);
  };

  const uploadCancelled = () => {
    xhr.abort();
    xhr = null;
  };

  const sendChunk = async chunk => {
    let data = await fetch(
      `http://localhost/videouploader/upload.php?filename=${window.filename}`,
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

  const uploadFile = chunk => {
    if (xhr !== null) {
      var fd = new FormData();
      fd.append("fileToUpload", chunk);

      sendChunk(fd)
        .then(data => {
          window.upload_counter++;
          uploadComplete();
        })
        .catch(e => uploadFailed());
    }
  };

  const uploadComplete = () => {
    if (window.upload_filearray.length > window.upload_counter) {
      uploadFile(window.upload_filearray[window.upload_counter]);
      updateProgress();
    } else {
      preview = false;
      document.getElementById("file").value = "";
      progressBarValue = progressBarMax;
    }
  };

  const updateProgress = () => {
    progressBarValue = window.upload_counter;
  };

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
    <input type="submit" id="submit" class="btn btn-success" value="Upload" />
  </div>
</form>
<div>
  {#if preview}
    <UploadPreview src={blobURL} />

    <p>Name:{video.name}</p>
    <p>Type:{video.type}</p>
    <p>Size:{video.size}</p>
    <p>Last Edited:{video.lastModified}</p>
  {/if}
</div>
<div>
  <ProgressBar
    max={progressBarMax}
    currentValue={progressBarValue}
    on:uploadcancelled={uploadCancelled} />
</div>
