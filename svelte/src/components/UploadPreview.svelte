<script>
  export let src;
  
</script>

{#if src !== ""}
<video src="{src}" width="300" controls></video>
{/if}