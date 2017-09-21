<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Recycle Bin';
  g_sSortableTableType = 'recycle';
  $( document ).ready( getSortableTable );
</script>
