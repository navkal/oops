<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/circuitObjects/editCircuitObject.php";
?>

<script>
  g_sSortableTableTitle = 'Transformers';
  g_sSortableTableType = 'transformer';

  if ( '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>' )
  {
    g_sSortableTableEditWhat = "Transformer";
  }

  $( document ).ready( getSortableTable );
</script>
