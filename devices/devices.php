<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/devices/editDevice.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemove.php';
?>

<script>
  g_sSortableTableTitle = 'Devices';
  g_sSortableTableType = 'device';

  if ( '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>' )
  {
    g_sSortableTableEditWhat = "Device";
    g_sRemoveCodeFolder = 'devices';
    g_bShowRemoveComment = true;
  }

  $( document ).ready( getSortableTable );



function customizeRemoveDialog( tRow )
{
  $( '#circuitDiv' ).remove();
  $( '#locationDiv' ).remove();

  // Show circuit
  var sHtml =
    '<div class="form-group" id="circuitDiv" >' +
      '<label for="circuit">Circuit</label>' +
      '<input type="text" class="form-control" id="circuit" value="' + tRow.source_path + '" readonly >' +
    '</div>';

  $( '#removeDialogForm' ).append( sHtml );
  $( '#circuitDiv' ).insertBefore( '#removeWhatDiv' );

  // Optionally show location
  if ( tRow.formatted_location )
  {
    sHtml =
      '<div class="form-group" id="locationDiv" >' +
        '<label for="location">Location</label>' +
        '<input type="text" class="form-control" id="location" value="' + tRow.formatted_location + '" disabled >' +
      '</div>';

    $( '#removeDialogForm' ).append( sHtml );
    $( '#locationDiv' ).insertBefore( '#removeCommentDiv' );
  }
}

</script>
