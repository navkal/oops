<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Activity';
  g_sSortableTableType = 'activity';
  g_bSortDescending = true;
  $( document ).ready( getSortableTable );
</script>
