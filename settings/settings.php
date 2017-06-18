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

</div>

<script>
  $( document ).ready( initSettings );

  function initSettings()
  {
    var tUser = JSON.parse( localStorage.getItem( 'signedInUser' ) );

    $( '#username' ).val( tUser.username );
    $( '#username' ).prop( 'readonly', true );

    $( '#readonlyRole' ).val( tUser.role );
    $( '#roleLabel' ).attr( 'for', 'readonlyRole' );

    $( '#role' ).hide();
  }
</script>
