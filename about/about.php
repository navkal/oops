<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/context.php";

  $command = quote( getenv( "PYTHON" ) ) . " database/getBuildTime.py 2>&1 " . $g_sContext;
  error_log( "==> command=" . $command );
  exec( $command, $output, $status );
  error_log( "==> output=" . print_r( $output, true ) );

  $sResult = $output[ count( $output ) - 1 ];
  $tObject = json_decode( $sResult );
  $sBuildDate = date( 'n/j/Y', $tObject->build_time );
?>

<div class="container">

  <div class="row">

    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-7">

      <div style="padding-bottom:35px";>
        <span class="h4">About Panel Spy</span>
        <span class="pull-right">
          <small>
            <?=$_SESSION['panelSpy']['context']['enterpriseFullname']?>
            <br/>
            <span class="text-muted">Build <?=$sBuildDate?></span>
          </small>
        </span>
      </div>

      <p>
        The electrical distribution network in a commercial or public building must constantly evolve to meet the changing needs of the tenants.
        Electricians who maintain and renovate the distribution network depend on precise knowledge of network structure to do their work.
        Typically, they piece that knowledge together from cryptic, hand-written notes found on electrical panel labels; from architectural drawings that may be inaccurate or out-of-date; and from time-consuming, hands-on testing of electrical connections.
      </p>

      <p>
        <b>Panel Spy</b> addresses these challenges by providing a dynamic, online model of your building’s electrical distribution network.
        It allows you to manage your entire inventory of panels, transformers, circuits, and devices, tracking their properties, connectivity, and locations.
        Most critically, <b>Panel Spy</b> allows you to update your inventory in real time.
      </p>

      <p>
        <b>Panel Spy</b> controls access to the online model through user roles that reflect worker responsibilities.
        An electrical intern, for example, might be assigned a role restricted to viewing and navigation, whereas a full-fledged technician would assume a role that allows alteration of the model and recording of contemporaneous notes about work being done.
      </p>

      <p>
        You can run <b>Panel Spy</b> in any modern browser, on a computer, smartphone or tablet.
      </p>

      <p>
        The <b>Panel Spy</b> team is proud to bring this innovative application to the world of building management.
        Your feedback is important to us.
        Please use the
        <a href="/?page=contact">
          <b>Contact</b>
        </a>
        page or e-mail
        <a href="mailto:<?php global $mailto; echo $mailto; ?>">
          <span class="glyphicon glyphicon-envelope" title="<?=$mailto?>" style="padding-left:3px; padding-right:3px;">
          </span>
        </a>
        to convey your suggestions and comments.
      </p>

      <p>
        Thank you for using <b>Panel Spy</b>!
      </p>
      <br/>

    </div>

    <div class="col-xs-12 col-sm-4 col-md-4 col-lg-5" style="padding-top:5px" >
      <img id="label" src="about/label.jpg" class="img-responsive" style="max-width:250px; margin:auto;" alt="Panel Label">
      <img id="hover" src="about/hover.jpg" class="img-responsive" style="max-width:250px; margin:auto; display:none;" alt="Distribution">
    </div>

  </div>
</div>

<script>
  $( '#label' ).mouseover( showHover );
  $( '#hover' ).mouseleave( showLabel );

  function showHover()
  {
    $( '#label' ).hide();
    $( '#hover' ).show();
  }

  function showLabel()
  {
    $( '#label' ).show();
    $( '#hover' ).hide();
  }
</script>
