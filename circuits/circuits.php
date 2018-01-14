<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/distributionObjects/editDistributionObject.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemove.php';
  $iVersion = time();
?>

<script src="distributionObjects/distributionObject.js?version=<?=$iVersion?>"></script>

<script>
  g_sSortableTableTitle = 'Circuits';
  g_sSortableTableType = 'Circuit';

  if ( '<?=in_array( $_SESSION['panelSpy']['user']['role'], ['Supervisor', 'Technician'] )?>' )
  {
    g_sSortableTableEditWhat = "Circuit";
    g_sRemoveCodeFolder = 'circuits';
    g_bShowRemoveComment = true;
  }

  $( document ).ready( getSortableTable );
</script>
