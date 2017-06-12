<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
    define( 'MAX_PASSWORD_LENGTH', 32 );
    define( 'MIN_PASSWORD_LENGTH', 8 );
  ?>

  <!-- Body -->
  <body>
    <div class="container">
      <div class="page-header">
        <h3>Update Password</h3>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <form onsubmit="handleClick(event); return false;" >
            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" class="form-control" id="username" value="<?=$_SESSION['panelSpy']['session']['username']?>" readonly>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="password" placeholder="New Password" required >
            </div>
            <div class="form-group">
              <label for="confirm" >Confirm</label>
              <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="confirm" placeholder="Confirm New Password" required >
            </div>
            <div style="text-align:center;" >
              <button id="update" type="submit" onclick="g_sAction='update'" class="btn btn-primary" >Update Password</button>
              <button id="cancel" type="submit" onclick="g_sAction='cancel'" class="btn btn-default" >Cancel</button>
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

    <script>
      document.title = "Update Password - Panel Spy";
    </script>

  </body>
</html>


<script>
  function handleClick( tEvent )
  {
    switch ( g_sAction )
    {
      case 'update':
        if ( validateInput() )
        {
          updatePassword();
        }
        break;

      case 'cancel':
      default:
        cancelSignIn();
        break;
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
      aMessages.push( 'Password length exceeds maximum of <?=MAX_PASSWORD_LENGTH?>.' );
    }

    if ( sPassword.length < <?=MIN_PASSWORD_LENGTH?> )
    {
      aMessages.push( 'Password length must contain at least <?=MIN_PASSWORD_LENGTH?> characters.' );
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

  function updatePassword()
  {
    alert( 'updatePassword' );
  }

  function cancelSignIn()
  {
    alert( 'cancelSignIn' );
  }

</script>
