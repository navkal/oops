<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Data Integrity';
  g_sSortableTableType = 'Integrity';
  $( document ).ready( getSortableTable );
</script>
