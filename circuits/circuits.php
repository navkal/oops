<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/circuitObjects/editCircuitObject.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemove.php';
  $iVersion = time();
?>

<script src="circuitObjects/circuitObject.js?version=<?=$iVersion?>"></script>

<script>
  g_sSortableTableTitle = 'Circuits';
  g_sSortableTableType = 'circuit';

  if ( '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>' )
  {
    g_sSortableTableEditWhat = "Circuit";
    g_sRemoveCodeFolder = 'circuits';
    g_bShowRemoveComment = true;
  }

  $( document ).ready( getSortableTable );
</script>
