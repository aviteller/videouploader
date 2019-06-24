<script>

  import { createEventDispatcher } from "svelte";

  let dispatch = createEventDispatcher();

  let dropzone = document.querySelector("#dropzone");


  // when file gets dropped data gets dispatched and sent to uploadform.svelte to be added to form
  const onDrop = e => {
    e.preventDefault();
    dispatch("droppedfile",e.dataTransfer.files);
  };

  // to prevent that page from loading up the file when being dragged in

  const onDragover = e => {
    e.stopPropagation();
    e.preventDefault();
  };
  const onDragenter = e => {
    e.stopPropagation();
    e.preventDefault();
    //e.target.classList.add("dragover");
  };
</script>

<style>
  .dropzone {
    border: 1px dotted black;
    width: 100%;
    height: 200px;
  }
  .dropzone{
    display: table;
    text-align: center
  }
  .drag-zone-text {
    display: table-cell;
    vertical-align: middle;
  }

</style>

<div
  class="dropzone"
  id="dropzone"
  on:drop={onDrop}
  on:dragenter={onDragenter}
  on:dragover={onDragover}>
  <h2 class="drag-zone-text">Drop zone</h2>
</div>
