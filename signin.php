<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<!DOCTYPE html>
<html>

  <!-- Head -->
  <?php
    require_once $_SERVER["DOCUMENT_ROOT"]."/../common/head.php";
  ?>

  <!-- Body -->
  <body>
    <div class="container">
      <div class="page-header">
        <p class="h3">Panel Spy</p>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <form action="/" method="post">
            <div style="text-align:center;" >
              <input type="text" name="username" class="form-control" placeholder="Username" required autofocus >
              <input type="password" name="password" class="form-control" placeholder="Password" required >
              <button type="submit" class="btn btn-primary btn-block">Sign in</button>
            </div>
          </form>
        </div>
      </div>

    </div>

    <script>
      document.title = "Sign In - Panel Spy";
    </script>

  </body>
</html>




<script>$('input').val('fake');</script>