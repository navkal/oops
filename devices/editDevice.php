<!-- Copyright 2017 Panel Spy.  All rights reserved. -->
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css" />

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<!-- Edit Location dialog -->
<div class="modal fade" id="editDialog" tabindex="-1" role="dialog" aria-labelledby="editDialogTitle">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editDialogTitle"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="editDialogForm" class="form-horizontal" onsubmit="onSubmitEditDialog(event); return false;" >
              <div class="form-group">
                <label for="source_path"></label>
                <div>
                  <select id="source_path" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="name"></label>
                <div>
                  <input type="text" class="form-control" id="name" maxlength="50">
                </div>
              </div>
              <div class="form-group">
                <label for="loc_new">Location</label>
                <div>
                  <select id="loc_new" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                  </select>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button id="editDialogSubmit" type="submit" class="btn btn-primary" form="editDialogForm" ></button>
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

  var g_sSourcePath = null;
  var g_sName = null;
  var g_sLocation = null;

  var g_bGotDropdowns = false;

  function initAddDialog()
  {
    g_sAction = 'add';
    initEditDialog( 'Device' );

    g_sSourcePath = '';
    g_sName = '';
    g_sLocation = '';
  }

  function initUpdateDialog( sDeviceId )
  {
    g_sAction = 'update';
    initEditDialog( 'Device' );

    // Find the selected row
    var iRow = 0;
    var tRow = null;
    do
    {
      tRow = g_aSortableTableRows[iRow];
      iRow ++
    }
    while( ( iRow < g_aSortableTableRows.length ) && ( tRow.id != sDeviceId ) );

    // Save values of selected row
    g_sSourcePath = tRow.source_path;
    g_sName = tRow.name;
    g_sLocation = tRow.loc_new;
  }

  function onShowEditDialog()
  {
    if ( ! g_bGotDropdowns )
    {
      g_bGotDropdowns = true;
      getDropdowns();
    }

    // Initialize input fields
    $( '#source_path' ).selectpicker( 'val', g_sSourcePath );
    $( '#name' ).val( g_sName );
    $( '#loc_new' ).selectpicker( 'val', g_sLocation );

    // Label dialog and submit button
    $( '#editDialogTitle' ).text( g_sSubmitLabel );
    $( '#editDialogSubmit' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

  var g_iBfDebug = null;
  function getDropdowns()
  {
    g_iBfDebug = Date.now();

    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "postSecurity", "" );

    $.ajax(
      "devices/getDeviceDropdowns.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( loadDropdowns )
    .fail( handleAjaxError );
  }

  function loadDropdowns( tRsp, sStatus, tJqXhr )
  {
    console.log( '===> dropdown retrieval elapsed time: ' + ( Date.now() - g_iBfDebug ) + 'ms' );
    var sHtmlSourcePath = '';
    var aSourcePaths = tRsp.source_paths;
    for ( var iPath in aSourcePaths )
    {
      var sPath = aSourcePaths[iPath];
      sHtmlSourcePath += '<option>' + sPath + '</option>';
    }

    var sHtmlLocation = '';
    var aLocations = tRsp.locations;
    for ( var iLoc in aLocations )
    {
      var tLoc = aLocations[iLoc];
      sHtmlLocation += '<option data-subtext="(' + tLoc.loc_old + ') ' + "'" + tLoc.loc_descr + "'" + '">' + tLoc.loc_new + '</option>';
    }

    $( '#source_path' ).html( sHtmlSourcePath );
    $( '#loc_new' ).html( sHtmlLocation );
    $('.selectpicker').selectpicker( 'refresh' );

    console.log( '===> dropdown initialization elapsed time: ' + ( Date.now() - g_iBfDebug ) + 'ms' );
  }

  function onShownEditDialog()
  {
    $( '#source_path' ).focus();
  }

  function onChangeControl( tEvent )
  {
    console.log( '==> changed: ' + tEvent.target.id );

    // Trim the value
    var tControl = $( tEvent.target );
    tControl.val( tControl.val().trim() );

    // Set flag
    g_bChanged = true;
  }

  function onSubmitEditDialog()
  {
    console.log( '==> submit' );

    if ( g_bChanged && validateInput() )
    {
      var tPostData = new FormData();
      if ( g_sLocationId )
      {
        tPostData.append( "id", g_sLocationId );
      }
      tPostData.append( "source_path", $( '#source_path' ).val() );
      tPostData.append( "name", $( '#name' ).val() );
      tPostData.append( "loc_new", $( '#loc_new' ).val() );

      // Post request to server
      $.ajax(
        "locations/" + g_sAction + "Device.php",
        {
          type: 'POST',
          processData: false,
          contentType: false,
          dataType : 'json',
          data: tPostData
        }
      )
      .done( submitEditDialogDone )
      .fail( handleAjaxError );
    }
  }

  function validateInput()
  {
    clearMessages();

    var aMessages = [];

    if ( false )
    {
      aMessages.push( 'Source path not valid??????????' );
      $( '#source_path' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    showMessages( aMessages );

    return ( aMessages.length == 0 );
  }

  function submitEditDialogDone( tRsp, sStatus, tJqXhr )
  {
    location.reload();
  }

</script>
