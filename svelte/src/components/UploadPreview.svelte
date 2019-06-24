<script>
//allows a preview of file  before upload
  export let src;
</script>

{#if src !== ''}
<div class="card">
  <video {src} width="400" height="300" controls />
</div>
{/if}
