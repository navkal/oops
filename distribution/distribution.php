<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Distribution';
  g_sSortableTableType = 'circuit';
  $( document ).ready( getSortableTable );
</script>
