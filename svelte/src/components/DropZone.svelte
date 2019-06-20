<script>

  import { createEventDispatcher } from "svelte";

  let dispatch = createEventDispatcher();

  let dropzone = document.querySelector("#dropzone");

  const onDrop = e => {
    e.preventDefault();
    dispatch("droppedfile",e.dataTransfer.files);
  };
  const onDragover = e => {
    e.stopPropagation();
    e.preventDefault();
  };
  const onDragenter = e => {
    e.stopPropagation();
    e.preventDefault();
    e.target.classList.add("dragover");
  };
</script>

<style>
  .dropzone {
    border: 1px dotted black;
    width: 200px;
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
  .dragover {
    background-color: red;
  }
</style>

<div
  class="dropzone"
  id="dropzone"
  on:drop={onDrop}
  on:dragenter={onDragenter}
  on:dragover={onDragover}>
  <p class="drag-zone-text">Drag zone</p>
</div>
