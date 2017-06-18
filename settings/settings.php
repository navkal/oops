<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/usernameRules.php";
  require_once $_SERVER["DOCUMENT_ROOT"] . "/users/passwordRules.php";
?>
<script src="../users/editUser.js?version=<?=time()?>"></script>

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

<script>
  $( document ).ready( initSettings );

  function initSettings()
  {
    g_sAction = 'update';

    var tUser = JSON.parse( localStorage.getItem( 'signedInUser' ) );

    $( '#username' ).val( tUser.username );
    $( '#username' ).prop( 'readonly', true );

    $( '#readonlyRole' ).val( tUser.role );
    $( '#roleLabel' ).attr( 'for', 'readonlyRole' );

    $( '#role' ).hide();
  }
</script>
