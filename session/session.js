// Copyright 2018 Panel Spy.  All rights reserved.

function submitCredentials()
{
  // Trim username
  var sUsername = $( '#username' ).val().trim();
  $( '#username' ).val( sUsername );

  // Get password
  var sPassword = $( '#password' ).val();

  // If we got username and password, submit credentials to the backend
  if ( ( sUsername != '' ) && ( sPassword != '' ) )
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", sUsername );
    tPostData.append( "password", sPassword );

    $.ajax(
      "session/signIn.php",
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( showMain )
    .fail( submitCredentialsFailed );
  }
}

function submitCredentialsFailed()
{
  signOut();
}

function signOut()
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( "postSecurity", "" );

  $.ajax(
    "session/signOut.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( showMain )
  .fail( handleAjaxError );
}

function showMain( tRsp, sStatus, tJqXhr )
{
  var sSession = JSON.stringify( tRsp );
  localStorage.setItem( 'panelSpy.session', sSession );
  location.assign( '/' );
}
