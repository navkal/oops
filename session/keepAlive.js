// Copyright 2017 Panel Spy.  All rights reserved.

// Initialize session state at load time
var tSession = JSON.parse( localStorage.getItem( 'panelSpy.session' ) );
var g_sSignInId = tSession.user.signInId;
var g_sEnterprise = tSession.context.enterprise;
var g_sFacility = tSession.context.facility ? tSession.context.facility : '';

console.log( '==> g_sSignInId=' + g_sSignInId );
console.log( '==> g_sEnterprise=' + g_sEnterprise );
console.log( '==> g_sFacility=' + g_sFacility );


// Start polling
$( document ).ready( live );

function live()
{
  setTimeout( poll, 2000 );
};

function poll()
{
  var tPostData = new FormData();
  tPostData.append( 'signInId', g_sSignInId );
  tPostData.append( 'enterprise', g_sEnterprise );
  tPostData.append( 'facility', g_sFacility );

  $.ajax(
    "../session/validateSession.php",
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
