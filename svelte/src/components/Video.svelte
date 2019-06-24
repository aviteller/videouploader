<script>

  // component for each video from the videos array


  import {  createEventDispatcher } from "svelte";

  // initializing the video object this was sent over from app.svelte
  export let video;

  // this allows details and function to be sent to other componetns
  let dispatch = createEventDispatcher();

  let videoSrc = `http://localhost/videouploader/uploads/${video.id}_${
    video.Name
  }`;


  // delete the video from the database then tell app.svelte to remove video from array
  const deleteVideo = () => {
    fetch(
      `http://localhost/videouploader/api.php?m=deleteVideo&id=${video.id}`
    ).then(data => {
      dispatch("deletevideo", video.id);
    });
  };
</script>

<div class="card">
  <h1>{video.Name}</h1><br>
  <small>Type: {video.Format}</small><br>
  <small>Size: {app.formatBytes(video.Size)}</small><br>
  <video src={videoSrc} width="400" height="300" controls />
  <button class="btn btn-sm btn-danger pull-right" on:click={deleteVideo}>x</button>
</div>
