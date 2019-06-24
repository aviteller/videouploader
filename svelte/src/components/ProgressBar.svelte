<script>

  // creates the progress bar based on data it recives from uploadForm.svelte

  // also contains the cancle button and send a function back to upload.svelte

  import { createEventDispatcher } from "svelte";

  let dispatch = createEventDispatcher();

  export let max;
  export let currentValue;


  const uploadCancelled = () => {
    dispatch("uploadcancelled", uploadCancelled);
  };
</script>

{#if max}
  {#if currentValue !== max}
    <progress {max} value={currentValue} />
    <button class="btn btn-danger" on:click={uploadCancelled}>
      Cancel Upload
    </button>
  {:else}
    <h4>Complete</h4>
  {/if}
{/if}
