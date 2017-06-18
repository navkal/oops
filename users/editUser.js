// Copyright 2017 Panel Spy.  All rights reserved.

function onSubmitUser()
{
  if ( validateUser() )
  {
    submitUser();
  }
}

function validateUser()
{
  clearMessages();

  var aMessages = validateUsername();
  if ( ( g_sAction == 'add' ) || ( $( '#password' ).val().length > 0 ) || ( $( '#confirm' ).val().length > 0 ) )
  {
    aMessages = aMessages.concat( validatePassword() );
  }

  showMessages( aMessages );

  return ( aMessages.length == 0 );
}

function submitUser()
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "username", $( '#username' ).val() );
  tPostData.append( "password", $( '#password' ).val() );
  tPostData.append( "role", $( '#role' ).val() );

  $.ajax(
    "users/" + g_sAction + "User.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( g_fnSubmitUserDone )
  .fail( handleAjaxError );
}
