<script>

  import { createEventDispatcher } from "svelte";

  let dispatch = createEventDispatcher();

  export let max;
  export let currentValue;

  let cancelled = false;
  
  const uploadCancelled = () => {

    dispatch("uploadcancelled", uploadCancelled)
  }

  
</script>

{#if max}
  {#if currentValue !== max}
    
    <progress {max} value={currentValue} />
    <button class="btn btn-danger" on:click={uploadCancelled}>Cancel Upload</button>
  {:else}
    <h4>Complete</h4>
  {/if}
{/if}
