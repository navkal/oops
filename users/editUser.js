// Copyright 2017 Panel Spy.  All rights reserved.

// Initialize field labels (etc.)
function formatLabels( nLabelColumnWidth )
{
  var aFields = $( '.form-control,.list-unstyled', '#editUserForm' );

  for ( var iField = 0; iField < aFields.length; iField ++ )
  {
    var tField = aFields[iField];
    var sKey = tField.id;
    var tRule = g_tPropertyRules[sKey];

    // If there is a rule for this element,
    if ( tRule )
    {
      var sLabel = tRule.label;
      $( 'label[for=' + sKey + ']' ).text( sLabel );
      if ( tField.tagName.toLowerCase() == 'input' )
      {
        $( tField ).attr( 'placeholder', sLabel );
      }
    }
  }

  // Set up display of password/confirm fields
  if ( g_sUsername == JSON.parse( localStorage.getItem( 'signedInUser' ) )['username'] )
  {
    $( '#oldPassword' ).closest( '.form-group' ).show();
    g_sFocusId = 'oldPassword';
  }
  else
  {
    $( '#oldPassword' ).closest( '.form-group' ).hide();

    if ( g_sAction == 'update' )
    {
      g_sFocusId = 'password';
    }
    else
    {
      g_sFocusId = 'username';
    }
  }

  var sNew = ( g_sAction == 'update' ) ? 'New ' : '';
  var sPassword = sNew + 'Password';
  var sConfirm = 'Confirm ' + sPassword;

  $( 'label[for=password]' ).text( sPassword );
  $( '#password' ).attr( 'placeholder', sPassword );
  $( 'label[for=confirm]' ).text( sConfirm );
  $( '#confirm' ).attr( 'placeholder', sConfirm );

  // Turn off autocomplete
  $( 'input', '#editUserForm' ).attr( 'autocomplete', 'off' );

  // Customize responsive layout
  $( '.form-group>label' ).removeClass().addClass( 'control-label' ).addClass( 'col-sm-' + nLabelColumnWidth );
  $( '.form-control,.fakeFormControl', '#editUserForm' ).parent().removeClass().addClass( 'col-sm-' + ( 12 - nLabelColumnWidth ) );
}

// Detect change of input controls
resetChangeHandler();

function resetChangeHandler()
{
  $( 'input,select,textarea' ).off( 'change' );
  $( 'input,select,textarea' ).on( 'change', onChangeControl );
}

function onChangeControl()
{
  g_bDoValidation = g_bDoValidation || ( g_sAction == 'update' );
}

function onSubmitUser()
{
  // If a control has been changed and input is valid, submit changes
  if ( g_bDoValidation && validateUser() )
  {
    submitUser();
  }
}

function validateUser()
{
  clearMessages();

  var aMessages = validateUsername();
  if ( ( g_sAction == 'add' ) || ( $( '#oldPassword' ).val().length > 0 ) || ( $( '#password' ).val().length > 0 ) || ( $( '#confirm' ).val().length > 0 ) )
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
  tPostData.append( "oldPassword", $( '#oldPassword' ).val() );
  tPostData.append( "password", $( '#password' ).val() );
  tPostData.append( "role", $( '#role' ).val() );
  tPostData.append( "status", $( '#status' ).val() );
  tPostData.append( "first_name", $( '#first_name' ).val() );
  tPostData.append( "last_name", $( '#last_name' ).val() );
  tPostData.append( "email_address", $( '#email_address' ).val() );
  tPostData.append( "organization", $( '#organization' ).val() );
  tPostData.append( "user_description", $( '#user_description' ).val() );

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
