// Copyright 2017 Panel Spy.  All rights reserved.


// Initialize field labels (etc.)
function formatLabels( nLabelColumnWidth )
{
  makeFieldLabels( $( '.form-control,.list-unstyled', '#editUserForm' ) );

  // Set up display of password/confirm fields
  if ( g_sUsername == JSON.parse( localStorage.getItem( 'panelSpy.session' ) )['user']['username'] )
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

function getAllFacilities()
{
  // Post request to server
  var tPostData = new FormData();
  tPostData.append( 'postSecurity', '' );

  $.ajax(
    "users/getAllFacilities.php",
    {
      type: 'POST',
      processData: false,
      contentType: false,
      dataType : 'json',
      data: tPostData
    }
  )
  .done( makeFacilityCheckboxes )
  .fail( handleAjaxError );
}

function makeFacilityCheckboxes( tRsp, sStatus, tJqXhr )
{
  var aFullnames = tRsp.sorted_fullnames;
  var tMap = tRsp.fullname_map;

  var sHtml = '';

  for ( var iFullname in aFullnames )
  {
    var sFullname = aFullnames[iFullname];
    var sName = tMap[sFullname];
    var sChecked = ( g_tAuthFacilities && g_tAuthFacilities.fullname_map[sFullname] ) ? 'checked ' : ''

    sHtml +=
      '<li>'
      +
        '<label class="checkbox checkbox-inline" >'
      +
          '<input type="checkbox" ' + sChecked + 'facility_name="' + sName + '" >'
      +
            sFullname
      +
        '</label>'
      +
      '</li>';
  }

  /*********** --> Additional fake checkboxes, to test layout of many facilities --> *********** /
  for ( i = 0; i < 20; i ++ ) { sHtml += '<li><label class="checkbox checkbox-inline" ><input type="checkbox" facility_name="test'+i+'" >test'+i+'</label></li>'; }
  /*********** <-- Additional fake checkboxes, to test layout of many facilities <-- ***********/

  $( '#auth_facilities' ).html( sHtml );

  resetChangeHandler();
}

function clearAllFacilities()
{
  $( '#auth_facilities' ).html( '' );
  resetChangeHandler();
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

  // If there are facilities checkboxes, but none are checked, report error
  var aCheckboxes = $( ':checkbox', '#auth_facilities' );
  var aChecked = $( ':checkbox:checked', '#auth_facilities' );
  if ( ( aCheckboxes.length > 0 ) && ( aChecked.length == 0 ) )
  {
    aMessages.push( 'You must select at least one Facility.' );
    aCheckboxes.closest( '.form-group' ).addClass( 'has-error' );
  }

  showMessages( aMessages );

  return ( aMessages.length == 0 );
}

function submitUser()
{
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

  // Retrieve list of authorized facilities from checkboxes
  var aChecked = $( ':checkbox:checked', '#auth_facilities' );
  var aAuth = [];
  for ( var iChk = 0; iChk < aChecked.length; iChk ++ )
  {
    var tChk = $( aChecked[iChk] );
    aAuth.push( tChk.attr( 'facility_name' ) );
  }
  tPostData.append( "auth_facilities", aAuth.join() );


  // Post request to server
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
