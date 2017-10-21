<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/sortableTable.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/locations/editLocation.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemove.php';
?>

<script>
  g_sSortableTableTitle = 'Locations';
  g_sSortableTableType = 'Location';
  if ( '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>' )
  {
    g_sSortableTableEditWhat = 'Location';
    g_sRemoveCodeFolder = 'locations';
    g_bShowRemoveComment = true;
  }

  $( document ).ready( getSortableTable );
</script>
