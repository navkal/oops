<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/sortableTable/sortableTable.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/circuitObjects/editCircuitObject.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . '/sortableTable/confirmRemove.php';
?>

<script>
  g_sSortableTableTitle = 'Transformers';
  g_sSortableTableType = 'transformer';

  if ( '<?=( $_SESSION['panelSpy']['user']['role'] == 'Technician' )?>' )
  {
    g_sSortableTableEditWhat = "Transformer";
    g_sRemoveCodeFolder = 'transformers';
    g_bShowRemoveComment = true;
  }

  $( document ).ready( getSortableTable );


  function customizeRemoveDialog( tRow )
  {
    // Show location
    $( '#locationDiv' ).remove();
    sHtml =
      '<div class="form-group" id="locationDiv" >' +
        '<label for="location">Location</label>' +
        '<input type="text" class="form-control" id="location" value="' + tRow.formatted_location + '" disabled >' +
      '</div>';
    $( '#removeDialogForm' ).append( sHtml );
    $( '#locationDiv' ).insertBefore( '#removeCommentDiv' );

    // Show description
    $( '#descriptionDiv' ).remove();
    var sHtml =
      '<div class="form-group" id="descriptionDiv" >' +
        '<label for="' + g_sSortableTableType + '_descr">' + g_sSortableTableEditWhat + ' Description</label>' +
        '<textarea id="descriptionDiv" class="form-control" disabled >' +
          eval( 'tRow.' + g_sSortableTableType + '_descr' ) +
        '</textarea>' +
      '</div>';
    $( '#removeDialogForm' ).append( sHtml );
    $( '#descriptionDiv' ).insertBefore( '#removeCommentDiv' );
  }
</script>
