<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Activity Log';
  g_sSortableTableType = 'Activity';
  $( document ).ready( getSortableTable );
</script>
