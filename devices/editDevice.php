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
                  <select id="source_path" class="form-control selectpicker show-tick" data-show-subtext="true" data-live-search="true">
                    <option>MSWB.1-PBA.1</option>
                    <option>MSWB.5-DHB.1-L42Csec2.31</option>
                    <option>MSWB.5-DHB.1-L42Csec2.38-T2C.L2C.23</option>
                    <option>MSWB.7-DG.1-P4Gsec1.15</option>
                    <option>MSWB.8-DL.5-L42B.33-T2B.L2B.1</option>
                    <option>MSWB.9-AMDP.7-HSHLA.100-TCC.HSLAsec1.20</option>
                    <option>MSWB.9-AMDP.8</option>
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
                <label for="location">Location</label>
                <div>
                  <select id="location" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                    <option data-subtext="1005">101</option>
                    <option data-subtext="1006">101-02</option>
                    <option data-subtext="1007">101-01</option>
                    <option data-subtext="1011">101-09</option>
                    <option data-subtext="1013">101-13</option>
                    <option data-subtext="1020">102-05</option>
                    <option data-subtext="1073">130-02</option>
                    <option data-subtext="1074">130-03</option>
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

  function initAddDialog()
  {
    g_sAction = 'add';
    initEditDialog( 'Device' );
    
    loadDropdowns();

    g_sSourcePath = '';
    g_sName = '';
    g_sLocation = '';

  }

  function initUpdateDialog( sDeviceId )
  {
    g_sAction = 'update';
    initEditDialog( 'Device' );
    loadDropdowns();

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

  function loadDropdowns()
  {
    console.log( '==> load dropdowns' );
  }

  function onShowEditDialog()
  {
    // Initialize input fields
    $( '#source_path' ).val( g_sSourcePath );
    $( '#name' ).val( g_sName );
    $( '#location' ).val( g_sLocation );

    // Label dialog and submit button
    $( '#editDialogTitle' ).text( g_sSubmitLabel );
    $( '#editDialogSubmit' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
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
      tPostData.append( "location", $( '#location' ).val() );

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
