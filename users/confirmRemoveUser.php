<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!-- Confirm Remove User dialog -->
<div class="modal fade" id="removeUserDialog" tabindex="-1" role="dialog" aria-labelledby="removeUserLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="removeUserLabel">Remove User</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="removeUserForm" onsubmit="removeUser(event); return false;" >
              <div class="form-group">
                <label for="removeUsername">Username</label>
                <input type="text" class="form-control" id="removeUsername" placeholder="Username" readonly >
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div style="text-align:center;" >
          <button type="submit" class="btn btn-danger" form="removeUserForm" >Remove User</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  var g_sUsername = null;
  function initConfirmRemove( sUsername )
  {
    g_sUsername = sUsername;
    $( '#removeUsername' ).val( g_sUsername );
  }

  $( '#removeUserDialog' ).on( 'shown.bs.modal', initUsername );
  function initUsername()
  {
    $( '#removeUsername' ).focus();
  }

  function removeUser()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", g_sUsername );

    $.ajax(
      "users/removeUser.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( removeDone )
    .fail( handleAjaxError );
  }

  function removeDone()
  {
    location.reload();
  }
</script>
