// Copyright 2017 Panel Spy.  All rights reserved.

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
  sObjectType = g_sSortableTableType.toLowerCase();
  var sHtml =
    '<div class="form-group" id="descriptionDiv" >' +
      '<label for="' + sObjectType + '_descr">' + g_sSortableTableEditWhat + ' Description</label>' +
      '<textarea id="descriptionDiv" class="form-control" disabled >' +
        eval( 'tRow.' + sObjectType + '_descr' ) +
      '</textarea>' +
    '</div>';
  $( '#removeDialogForm' ).append( sHtml );
  $( '#descriptionDiv' ).insertBefore( '#removeCommentDiv' );
}
