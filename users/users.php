<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Users';
  g_sSortableTableType = 'user';
  g_bHideTopologyLink = true;
  $( document ).ready( getSortableTable );
</script>
