<?php
  // Copyright 2017 Panel Spy.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";

  define( 'MIN_PASSWORD_LENGTH', 6 );
  define( 'MAX_PASSWORD_LENGTH', 128 );
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

    if ( sPassword.length > <?=MAX_PASSWORD_LENGTH?> )
    {
      aMessages.push( 'Password may contain at most <?=MAX_PASSWORD_LENGTH?> characters.' );
      tPassword.closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( sPassword.length < <?=MIN_PASSWORD_LENGTH?> )
    {
      aMessages.push( 'Password must contain at least <?=MIN_PASSWORD_LENGTH?> characters.' );
      tPassword.closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( sPassword.indexOf( ' ' ) != -1 )
    {
      aMessages.push( 'Password may not contain spaces.' );
      tPassword.closest( '.form-group' ).addClass( 'has-error' );
    }

    if ( sPassword.indexOf( '"' ) != -1 )
    {
      aMessages.push( 'Password may not contain quotes.' );
      tPassword.closest( '.form-group' ).addClass( 'has-error' );
    }

    return aMessages;
  }
</script>
