// Copyright 2017 Panel Spy.  All rights reserved.

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
  var tRole = $( '#role' ).is( ':visible' ) ? $( '#role' ) : $( '#admin' );
  tPostData.append( "role", tRole.val() );

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
  .done( eval( g_sAction + 'Done' ) )
  .fail( handleAjaxError );
}

function addDone( tRsp, sStatus, tJqXhr )
{
  if ( tRsp.unique )
  {
    location.reload();
  }
  else
  {
    var aMessages = [ "Username '" + tRsp.username + "' is not available." ];
    showMessages( aMessages );
  }
}

function updateDone( tRsp, sStatus, tJqXhr )
{
  location.reload();
}
