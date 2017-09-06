<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/devices/editDevice.php";
?>

<script>
  g_sSortableTableTitle = 'Devices';
  g_sSortableTableType = 'device';

  if ( '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>' )
  {
    g_sSortableTableEditWhat = "Device";
  }

  $( document ).ready( getSortableTable );
</script>
