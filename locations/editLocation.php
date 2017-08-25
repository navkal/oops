<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

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
                <label for="loc_new"></label>
                <div>
                  <input type="text" class="form-control" id="loc_new" maxlength="50">
                </div>
              </div>
              <div class="form-group">
                <label for="loc_old"></label>
                <div>
                  <input type="text" class="form-control" id="loc_old" maxlength="50">
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

  var g_sLocation = null;
  var g_sOldLocation = null;
  var g_sDescription = null;
  var g_sLocationId = null;

  function initAddDialog()
  {
    g_sAction = 'add';
    initEditDialog( 'Location' );

    g_sLocation = '';
    g_sOldLocation = '';
    g_sDescription = '';
  }

  function initUpdateDialog( sLocationId )
  {
    g_sAction = 'update';
    initEditDialog( 'Location' );

    // Find the selected row
    var iRow = 0;
    var tRow = null;
    do
    {
      tRow = g_aSortableTableRows[iRow];
      iRow ++
    }
    while( ( iRow < g_aSortableTableRows.length ) && ( tRow.id != sLocationId ) );

    // Save values of selected row
    g_sLocationId = tRow.id;
    g_sLocation = tRow.loc_new;
    g_sOldLocation = tRow.loc_old;
    g_sDescription = tRow.loc_descr;
  }

  function onShowEditDialog()
  {
    // Initialize input fields
    $( '#loc_new' ).val( g_sLocation );
    $( '#loc_old' ).val( g_sOldLocation );
    $( '#loc_descr' ).val( g_sDescription );

    // Label dialog and submit button
    $( '#editDialogTitle' ).text( g_sSubmitLabel );
    $( '#editDialogSubmit' ).text( g_sSubmitLabel );

    // Clear messages
    clearMessages();
  }

  function onShownEditDialog()
  {
    $( '#loc_new' ).focus();
  }

  function onChangeControl()
  {
    console.log( '==> change' );
    g_bChanged = true;
  }

  function onSubmitEditDialog()
  {
    console.log( '==> submit' );

    if ( g_bChanged )
    {
      var tPostData = new FormData();
      if ( g_sLocationId )
      {
        tPostData.append( "id", g_sLocationId );
      }
      tPostData.append( "loc_new", $( '#loc_new' ).val() );
      tPostData.append( "loc_old", $( '#loc_old' ).val() );
      tPostData.append( "loc_descr", $( '#loc_descr' ).val() );

      // Post request to server
      $.ajax(
        "locations/" + g_sAction + "Location.php",
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

  function submitEditDialogDone()
  {
    console.log( '=========> DONE' );
  }

</script>
