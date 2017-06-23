<?php
  // Copyright 2017 Panel Spy.  All rights reserved.
  define( 'MIN_PASSWORD_LENGTH', 6 );
  define( 'MAX_PASSWORD_LENGTH', 32 );
?>
<script>
  function validatePassword()
  {
    var tOldPassword = $( '#oldPassword' );
    var sOldPassword = tOldPassword.val();
    var tPassword = $( '#password' );
    var sPassword = tPassword.val();
    var tConfirm = $( '#confirm' );
    var sConfirm = tConfirm.val();

    var aMessages = [];

    if ( tOldPassword.is( ':visible' ) && ( sOldPassword == '' ) )
    {
      aMessages.push( 'Old Password is required.' );
      tOldPassword.closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( sPassword != sConfirm )
    {
      aMessages.push( 'Passwords do not match.' );
      tConfirm.closest( '.form-group' ).addClass( 'has-error' );
      tPassword.closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( ( sPassword.length > <?=MAX_PASSWORD_LENGTH?> ) || ( sPassword.length < <?=MIN_PASSWORD_LENGTH?> ) || ( sPassword.indexOf( '"' ) != -1 ) )
    {
      aMessages.push( 'Password must contain between <?=MIN_PASSWORD_LENGTH?> and <?=MAX_PASSWORD_LENGTH?> characters other than quote (").' );
      tPassword.closest( '.form-group' ).addClass( 'has-error' );
    }

    return aMessages;
  }
</script>
