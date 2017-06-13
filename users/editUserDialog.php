<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/session/passwordRules.php";
?>

<!-- Edit User dialog -->
<div class="modal fade" id="editUserDialog" tabindex="-1" role="dialog" aria-labelledby="editUserLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editUserLabel"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form onsubmit="handleClick(event); return false;" >
              <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" value="<?=$sUsername?>" <?=$sUsernameReadonly?>  placeholder="Username" >
              </div>
              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="password" placeholder="Password" >
              </div>
              <div class="form-group">
                <label for="confirm" >Confirm</label>
                <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="confirm" placeholder="Confirm Password" >
              </div>
              <div style="text-align:center;" >
                <button id="submit" type="submit" class="btn btn-primary" ><?=$sSubmitLabel?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <br/>

        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div id="messages" class="alert alert-danger" style="display:none" role="alert">
              <ul id="messageList">
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<script>
  var g_sAction = '<?=$sSubmitAction?>';
  $( '#editUserDialog' ).on( 'show.bs.modal', onShow );
  $( '#editUserDialog' ).on( 'shown.bs.modal', onShown );
  $( '#editUserDialog' ).on( 'hide.bs.modal', onHide );
  $( '#editUserDialog' ).on( 'hidden.bs.modal', onHidden );

  function onShow()
  {
    console.log( 'onShow' );
    clearMessages();
  }
  function onShown()
  {
    console.log( 'onShown' );
  }
  function onHide()
  {
    console.log( 'onHide' );
  }
  function onHidden()
  {
    console.log( 'onHidden' );
  }
  function handleClick()
  {
    console.log( 'submitUser: action=' + g_sAction );
    if ( validateInput() )
    {
      submitUser();
    }
  }



  function validateInput()
  {
    clearMessages();
    var aMessages = validatePassword();
    showMessages( aMessages );
    return ( aMessages.length == 0 );
  }


  function validatePassword()
  {
    var tPassword = $( '#password' );
    var sPassword = tPassword.val();
    var tConfirm = $( '#confirm' );
    var sConfirm = tConfirm.val();

    var aMessages = [];
    if ( sPassword != sConfirm )
    {
      aMessages.push( 'Values do not match.' );
      tConfirm.parent().addClass( 'has-error' );
    }

    if ( sPassword.length > <?=MAX_PASSWORD_LENGTH?> )
    {
      aMessages.push( 'Password may contain at most <?=MAX_PASSWORD_LENGTH?> characters.' );
    }

    if ( sPassword.length < <?=MIN_PASSWORD_LENGTH?> )
    {
      aMessages.push( 'Password must contain at least <?=MIN_PASSWORD_LENGTH?> characters.' );
    }

    if ( sPassword.indexOf( ' ' ) != -1 )
    {
      aMessages.push( 'Password may not contain spaces.' );
    }

    if ( aMessages.length )
    {
      tPassword.parent().addClass( 'has-error' );
    }

    return aMessages;
  }


  function clearMessages()
  {
    $( ".has-error" ).removeClass( "has-error" );
    $( "#messages" ).css( "display", "none" );
    $( "#messageList" ).html( "" );
  }

  function showMessages( aMessages )
  {
    if ( aMessages.length > 0 )
    {
      for ( var index in aMessages )
      {
        $( "#messageList" ).append( '<li>' + aMessages[index] + '</li>' );
      }
      $( "#messages" ).css( "display", "block" );
    }
  }




  function submitUser()
  {
    // Post request to server
    var tPostData = new FormData();
    tPostData.append( "username", $( '#username' ).val() );
    tPostData.append( "password", $( '#password' ).val() );

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
    .done( reloadPage )
    .fail( handleAjaxError );
  }





  function reloadPage()
  {
    console.log('reloadPage');
    location.reload();
  }
</script>
