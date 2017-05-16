<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Panels';
  g_sSortableTableType = 'panel';
  $( document ).ready( getSortableTable );
</script>
