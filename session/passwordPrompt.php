<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/head.php";
  ?>

  <!-- Body -->
  <body>
    <div class="container">
      <div class="page-header">
        <h3>Update Password</h3>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <form onsubmit="updatePassword(event); return false;" >
            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" class="form-control" id="username" value="<?=$_SESSION['panelSpy']['session']['username']?>" readonly>
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" class="form-control" id="password" placeholder="New Password">
            </div>
            <div class="form-group">
              <label for="confirm" >Confirm</label>
              <input type="password" class="form-control" id="confirm" placeholder="Confirm New Password">
            </div>
            <div style="text-align:center;" >
              <button id="update" type="submit" onclick="g_sAction='update'" class="btn btn-primary" >Update Password</button>
              <button id="cancel" type="submit" onclick="g_sAction='cancel'" class="btn btn-default" >Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      document.title = "Update Password - Panel Spy";
    </script>

  </body>
</html>

<script>
  function updatePassword( tEvent )
  {
    alert( g_sAction );

  }
</script>
