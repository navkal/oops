// Copyright 2017 Panel Spy.  All rights reserved.

var g_sSignInId = JSON.parse( localStorage.getItem( 'panelSpy.session' ) )['user']['signInId'];

$( document ).ready( live );

function live()
{
  setTimeout( poll, 2000 );
};

function poll()
{
  var tPostData = new FormData();
  tPostData.append( 'signInId', g_sSignInId );

  $.ajax(
    "../session/signedIn.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( liveOrDie )
  .fail( die );
}

function liveOrDie( tRsp, sStatus, tJqXhr )
{
  if ( tRsp )
  {
    live();
  }
  else
  {
    die();
  }
}

function die()
{
  // Try to close the window
  window.close();

  // In case window did not close, go to sign-in page
  location.assign( '/' );
}
