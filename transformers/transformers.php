<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/distributionObjects/editDistributionObject.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemove.php';
  $iVersion = time();
?>

<script src="distributionObjects/distributionObject.js?version=<?=$iVersion?>"></script>

<script>
  g_sSortableTableTitle = 'Transformers';
  g_sSortableTableType = 'Transformer';

  if ( '<?=in_array( $_SESSION['panelSpy']['user']['role'], ['Supervisor', 'Technician'] )?>' )
  {
    g_sSortableTableEditWhat = "Transformer";
    g_sRemoveCodeFolder = 'transformers';
    g_bShowRemoveComment = true;
  }

  $( document ).ready( getSortableTable );
</script>
