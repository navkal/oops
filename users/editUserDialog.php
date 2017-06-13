<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/passwordRules.php";
?>

<!-- Edit User dialog -->
<div class="modal fade" id="editUserDialog" tabindex="-1" role="dialog" aria-labelledby="editUserLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editUserLabel"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form onsubmit="return submitUser(event);" >
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" value="<?=$_SESSION['panelSpy']['user']['username']?>" <?=$sUsernameReadonly?> >
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="password" placeholder="New Password" >
              </div>
              <div class="form-group">
                <label for="confirm" >Confirm</label>
                <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="confirm" placeholder="Confirm New Password" >
              </div>
              <div style="text-align:center;" >
                <button id="submit" type="submit" class="btn btn-primary" ><?=$sSubmitLabel?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <br/>

        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div id="messages" class="alert alert-danger" style="display:none" role="alert">
              <ul id="messageList">
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<script>
  var g_sAction = '<?=$sSubmitAction?>';
  $( '#editUserDialog' ).on( 'show.bs.modal', onShow );
  $( '#editUserDialog' ).on( 'shown.bs.modal', onShown );
  $( '#editUserDialog' ).on( 'hide.bs.modal', onHide );
  $( '#editUserDialog' ).on( 'hidden.bs.modal', onHidden );

  function onShow()
  {
    console.log( 'onShow' );
  }
  function onShown()
  {
    console.log( 'onShown' );
  }
  function onHide()
  {
    console.log( 'onHide' );
  }
  function onHidden()
  {
    console.log( 'onHidden' );
  }
  function submitUser()
  {
    var bSuccess = true;
    console.log( 'submitUser: action=' + g_sAction );
    return bSuccess;
  }
</script>
