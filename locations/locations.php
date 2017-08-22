<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
?>

<script>
  g_sSortableTableTitle = 'Locations';
  g_sSortableTableType = 'location';
  g_bShowAddLocationButton = '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>';
  $( document ).ready( getSortableTable );
</script>
