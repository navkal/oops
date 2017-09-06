<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/editCircuitObject.php";
?>

<script>
  g_sSortableTableTitle = 'Circuits';
  g_sSortableTableType = 'circuit';

  if ( '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>' )
  {
    g_sSortableTableEditWhat = "Circuit";
  }

  $( document ).ready( getSortableTable );
</script>
