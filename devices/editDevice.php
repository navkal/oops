<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<script src="lib/combobox/combobox.js"></script>
<link rel="stylesheet" href="lib/combobox/combobox.css">

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
                <div id="source_path_container" >
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
                <div id="loc_new_container" >
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
  var g_tRsp = null;

  function onShowEditDialog()
  {
    showSpinner();

    $( '#source_path_container' ).html( '<select id="source_path" class="form-control combobox" ></select>' );
    $( '#loc_new_container' ).html( '<select id="loc_new" class="form-control combobox" ></select>' );

    if ( ! g_bGotDropdowns )
    {
      g_bGotDropdowns = true;
      getDropdowns();
    }
    else
    {
      makeDropdowns();
    }
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
    .done( makeDropdowns )
    .fail( handleAjaxError );
  }

  function makeDropdowns( tRsp, sStatus, tJqXhr )
  {
    if ( tRsp )
    {
      g_tRsp = tRsp;
    }
    else
    {
      tRsp = g_tRsp;
    }

    console.log( '===> dropdown retrieval elapsed time: ' + ( Date.now() - g_iBfDebug ) + 'ms' );
    var sHtmlSourcePath = '<option></option>';
    var aSourcePaths = tRsp.source_paths;
    for ( var iPath in aSourcePaths )
    {
      var sPath = aSourcePaths[iPath];
      sHtmlSourcePath += '<option>' + sPath + '</option>';
    }

    var sHtmlLocation = '<option></option>';
    var aLocations = tRsp.locations;
    for ( var iLoc in aLocations )
    {
      var tLoc = aLocations[iLoc];
      sHtmlLocation += '<option value="' + tLoc.id + '" >' + formatLocation( tLoc ) + '</option>';
    }

    $( '#source_path' ).html( sHtmlSourcePath );
    $( '#loc_new' ).html( sHtmlLocation );

    console.log( '===> dropdown initialization elapsed time: ' + ( Date.now() - g_iBfDebug ) + 'ms' );

    loadEditDialog()
  }

  function loadEditDialog()
  {
    initEditDialog( 'Device' );

    switch( g_sAction )
    {
      case 'add':
        initAddDialog();
        break;

      case 'update':
        initUpdateDialog();
        break;
    }

    // Initialize combobox
    $( '#source_path' ).val( g_sSourcePath );
    $( '#loc_new' ).val( g_sLocation );
    $( '.combobox' ).combobox(
      {
        bsVersion: '3',
        appendId: '_input'
      }
    );
    resetChangeHandler();

    // Fix side effects of combobox initialization
    $( '.combobox-container .col-sm-9' ).removeClass( 'col-sm-9' );
    $( '.add-on' ).removeClass( 'add-on' ).addClass( 'input-group-addon' );
    $( 'label[for=source_path]' ).attr( 'for', 'source_path_input' );
    $( 'label[for=loc_new]' ).attr( 'for', 'loc_new_input' );
    $( '#source_path_input' ).prop( 'required', true );
    $( '#name' ).prop( 'required', true );
    $( '#loc_new_input' ).prop( 'required', true );

    // Initialize input fields
    $( '#name' ).val( g_sName );
    // $('#source_path,#source_path_input').val( g_sSourcePath );
    // $('#source_path').data( 'combobox' ).refresh();
    // $('#loc_new').val( g_sLocation );
    // $('#loc_new').data( 'combobox' ).refresh();

    // Label dialog and submit button
    $( '#editDialogTitle' ).text( g_sSubmitLabel );
    $( '#editDialogSubmit' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

  function initAddDialog()
  {
    g_sSourcePath = "\n";
    g_sName = '';
    g_sLocation = "\n";
  }

  function initUpdateDialog()
  {
    // Find the selected row
    var iRow = 0;
    var tRow = null;
    do
    {
      tRow = g_aSortableTableRows[iRow];
      iRow ++
    }
    while( ( iRow < g_aSortableTableRows.length ) && ( tRow.id != g_sUpdateTarget ) );

    // Save values of selected row
    g_sSourcePath = tRow.source_path;
    g_sName = tRow.name;
    g_sLocation = formatLocation( tRow );
 }

  function formatLocation( tLoc )
  {
    return tLoc.loc_new + ' (' + tLoc.loc_old + ') ' + "'" + tLoc.loc_descr + "'";
  }

  function onShownEditDialog()
  {
    hideSpinner();
  }

  function onChangeControl( tEvent )
  {
    console.log( '==> changed: ' + tEvent.target.id );

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
      tPostData.append( 'source_path', $( '#source_path' ).val() );
      tPostData.append( 'name', $( '#name' ).val() );
      tPostData.append( 'loc_id', $( '#loc_new' ).val() );

      // Post request to server
      $.ajax(
        'devices/' + g_sAction + 'Device.php',
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
    showMessages( aMessages );
    return ( aMessages.length == 0 );
  }

  function submitEditDialogDone( tRsp, sStatus, tJqXhr )
  {
    location.reload();
  }

</script>
