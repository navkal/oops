<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<!-- Edit Note dialog -->
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
                <label for="note" ></label>
                <div>
                  <textarea class="form-control" id="note" maxlength="500" ></textarea>
                </div>
              </div>
              <button id="editDialogFormSubmitButton" type="submit" style="display:none" ></button>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button type="button" id="editDialogFormSubmitProxy" class="btn btn-primary" onclick="$('#editDialogFormSubmitButton').click()" ></button>
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
  function onShowEditDialog()
  {
    initEditDialog( 1 );

    $( '#note' ).val('');

    // Clear messages
    clearMessages();
  }

  function onShownEditDialog()
  {
    $( '#note' ).focus();
  }

  function onChangeControl( tEvent )
  {
    // Trim the value
    var tControl = $( tEvent.target );
    tControl.val( tControl.val().trim() );

    // Set flag
    g_bChanged = true;
  }

  function onSubmitEditDialog()
  {
    if ( g_bChanged && validateInput() )
    {
      var tPostData = new FormData();
      tPostData.append( 'object_type', g_sSortableTableParams['target_object_type'] );
      tPostData.append( 'object_id', g_sSortableTableParams['target_object_id'] );
      tPostData.append( 'note', $( '#note' ).val() );

      showSpinner();

      // Post request to server
      $.ajax(
        '/activity/addNote.php',
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

    if ( $( '#note' ).val() == '' )
    {
      aMessages.push( 'Note cannot be empty.' );
      $( '#note' ).closest( '.form-group' ).addClass( 'has-error' );
    }

    showMessages( aMessages );

    return ( aMessages.length == 0 );
  }
</script>
