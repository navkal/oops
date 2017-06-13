<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Users';
  g_sSortableTableType = 'user';
  g_bShowTopologyLink = false;
  g_bShowAddUserButton = true;
  $( document ).ready( getSortableTable );
</script>
