<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/usernameRules.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/passwordRules.php";
?>

<div class="container">
  <p>
    <span class="h4">Settings</span>
  </p>
  <br/>

  <?php
    include $_SERVER["DOCUMENT_ROOT"] . "/users/editUserForm.php";
  ?>

  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div style="text-align:center;" >
        <button type="submit" class="btn btn-primary" form="editUserForm" >Submit</button>
        <button type="button" class="btn btn-default" onclick="location.reload();" >Clear</button>
      </div>
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

<div class="modal fade" id="settingsSuccessDialog" tabindex="-1" role="dialog" aria-labelledby="settingsSuccessLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title text-success" id="settingsSuccessLabel">Success</h4>
      </div>
      <div class="modal-body bg-success">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <p class="text-success" ><b>Your settings have been saved.</b></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <form submit="return false;">
          <input type="text" id="dummyInput" style="width:0px; height:0px; opacity:0%; border:none">
          <button type="submit" class="btn btn-primary" data-dismiss="modal" >OK</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  var g_sAction = null;
  var g_fnSubmitUserDone = null;
  var g_bDoValidation = null;

  $( document ).ready( initSettings );
  $( '#settingsSuccessDialog' ).on( 'shown.bs.modal', onShownSuccessDialog );
  $( '#settingsSuccessDialog' ).on( 'hide.bs.modal', onOkSuccessDialog );


  function initSettings()
  {
    g_sAction = 'update';
    g_bDoValidation = false;
    g_fnSubmitUserDone = settingsDone;

    var tUser = JSON.parse( localStorage.getItem( 'signedInUser' ) );

    $( '#username' ).val( tUser.username );
    $( '#username' ).prop( 'readonly', true );

    $( '#password' ).focus();

    $( '#role,#readonlyRole' ).val( tUser.role );
    $( '#roleLabel' ).attr( 'for', 'readonlyRole' );

    $( '#role' ).hide();
  }

  function settingsDone( tRsp, sStatus, tJqXhr )
  {
    $( '#settingsSuccessDialog' ).modal( { backdrop:'static' } )
  }

  function onShownSuccessDialog()
  {
    $( '#dummyInput' ).focus();
  }

  function onOkSuccessDialog()
  {
    location.reload();
  }
</script>

<script src="../users/editUser.js?version=<?=time()?>"></script>
