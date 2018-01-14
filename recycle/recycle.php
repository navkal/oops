<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . '/recycle/confirmRestore.php';
?>

<script src="recycle/restore.js?version=<?=$iVersion?>"></script>

<script>
  g_sSortableTableTitle = 'Recycle Bin';
  g_sSortableTableType = 'Recycle';
  $( document ).ready( getSortableTable );
</script>
