<script>

  // main file where svelte app is run from



  // importing nesscery compontants and functions
  import Navbar from "./components/Navbar.svelte";
  import UploadForm from "./components/UploadForm.svelte";
  import Video from "./components/Video.svelte";
  import { onMount } from "svelte";

// initilzing the videos arrays
  let videos = [];

  onMount(() => {
    //on mount function allows function to load when this component is loaded
    getVideos();
  });

  const addVideo = e => {
    // takes the new added video and append it to end of videos array
    const newVideo = e.detail;
    videos = [...videos, newVideo];
  };

  const getVideos = async () => {
    // gets all the data from the api and adds it to the videos array
    let res = await fetch(`http://localhost/videouploader/api.php?m=getVideos`);
    videos = await res.json();
    if(videos.message) {
      //if no videos array is reset to empty array
      videos = [];
    }
  };

  const deleteVideo = e => {
    // removes video from array based on id
    videos = videos.filter(video => video.id !== e.detail);
  };
</script>

<style>

</style>
<!-- svelte works similar to react by calling components for example calling <Navbar /> will call the navbar components -->
<Navbar />
<div class="container">
<!-- some svelte components allow for params or functions -->
  <UploadForm on:addvideo={addVideo} />
<!-- if syntax for svelte templating -->
  {#if videos.length !== 0}
  <!-- foreach syntax -->
    {#each videos as video}
    <!-- sending to the video compontent a video object -->
    <!-- the on: syntax with svelte works similar to vanilla javascript i.e on:click is like onClick  -->
      <Video {video} on:deletevideo={deleteVideo} />
    {/each}
  {/if}
</div>
