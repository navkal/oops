<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<!-- Add Location button -->
<button id="addLocationButton" class="btn btn-default btn-sm pull-right" onclick="initAddLocationDialog()" data-toggle="modal" data-target="#editLocationDialog" data-backdrop="static" data-keyboard=false style="display:none" >
  <span class="glyphicon glyphicon-plus"></span> Add Location
</button>

<!-- Edit Location dialog -->
<div class="modal fade" id="editLocationDialog" tabindex="-1" role="dialog" aria-labelledby="editLocationLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editLocationLabel"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="editLocationForm" class="form-horizontal" onsubmit="onSubmitLocation(event); return false;" >
              <div class="form-group">
                <label for="loc_new"></label>
                <div>
                  <input type="text" class="form-control" id="loc_new" maxlength="10">
                </div>
              </div>
              <div class="form-group">
                <label for="loc_old"></label>
                <div>
                  <input type="text" class="form-control" id="loc_old" maxlength="10">
                </div>
              </div>
              <div class="form-group">
                <label for="loc_descr" ></label>
                <div>
                  <textarea class="form-control" id="loc_descr" maxlength="512" ></textarea>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button id="submitLocation" type="submit" class="btn btn-primary" form="editLocationForm" ></button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
        <br/>
        <div id="messages" class="alert alert-danger" style="text-align:left; display:none" role="alert">
          <ul id="messageList">
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  var g_sAction = null;
  var g_sSubmitLabel = null;

  var g_sLocation = null;
  var g_sOldLocation = null;
  var g_sDescription = null;

  $( '#editLocationDialog' ).on( 'show.bs.modal', onShow );
  $( '#editLocationDialog' ).on( 'shown.bs.modal', onShown );

  function initLocationDialog()
  {
    makeFormLabels( $( '.form-control', '#editLocationForm' ) );

    // Turn off autocomplete
    $( 'input', '#editLocationForm' ).attr( 'autocomplete', 'off' );

    // Customize responsive layout
    var nLabelColumnWidth = 3;
    $( '.form-group>label' ).removeClass().addClass( 'control-label' ).addClass( 'col-sm-' + nLabelColumnWidth );
    $( '.form-control', '#editLocationForm' ).parent().removeClass().addClass( 'col-sm-' + ( 12 - nLabelColumnWidth ) );
  }

  function initAddLocationDialog()
  {
    initLocationDialog();

    g_sAction = 'add';
    g_sSubmitLabel = 'Add Location';

    g_sLocation = '';
    g_sOldLocation = '';
    g_sDescription = '';
  }

  function initUpdateLocationDialog( sLocationId )
  {
    initLocationDialog();

    g_sAction = 'update';
    g_sSubmitLabel = 'Update Location';

    var iRow = 0;
    var tRow = null;
    do
    {
      tRow = g_aSortableTableRows[iRow];
      iRow ++
    }
    while( ( iRow < g_aSortableTableRows.length ) && ( tRow.id != sLocationId ) );

    g_sLocation = tRow.loc_new;
    g_sOldLocation = tRow.loc_old;
    g_sDescription = tRow.loc_descr;
  }

  function onShow()
  {
    // Initialize input fields
    $( '#loc_new' ).val( g_sLocation );
    $( '#loc_old' ).val( g_sOldLocation );
    $( '#loc_descr' ).val( g_sDescription );

    // Label dialog and submit button
    $( '#editLocationLabel' ).text( g_sSubmitLabel );
    $( '#submitLocation' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

  function onShown()
  {
    $( '#editLocationDialog input:first-of-type' ).focus();
  }

  function addDone( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp.unique )
    {
      location.reload();
    }
    else
    {
      var aMessages = [ "Location '" + tRsp.id + "' is not available." ];
      showMessages( aMessages );
    }
  }

  function updateDone( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp.location.messages.length == 0 )
    {
      location.reload();
    }
    else
    {
      // Show error messages
      showMessages( tRsp.location.messages );
    }
  }
</script>
