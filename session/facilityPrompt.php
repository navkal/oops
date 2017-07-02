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
        <img src="brand.ico" class="img-responsive" alt="Panel Spy" style="width:50%; max-width:250px; margin:auto">
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <form onsubmit="alert('form'); return false;" >
            <div style="text-align:center;" >
              <select>
                <option value="ahs">Andover High School</option>
                <option value="bancroft">Bancroft Elementary School</option>
              </select>
              <button type="submit" onclick="alert('submit');" class="btn btn-primary" >Continue</button>
              <button type="button" onclick="alert('quit');" class="btn btn-default" >Quit</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      document.title = "Choose Facility - Panel Spy";
    </script>

  </body>
</html>
