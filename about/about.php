<!-- Copyright 2018 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<div class="container">

  <div class="row">

    <div class="col-xs-12 col-sm-8 col-md-8 col-lg-7">

      <div style="padding-bottom:18px";>
        <span class="h4">About Panel Spy</span>
        <br/>
      </div>

      <p>
        The electrical distribution network of a commercial or public building must constantly evolve to meet tenants' changing needs.
        Electricians who maintain and renovate the network depend on precise knowledge of its structure to do their work.
        Typically, they piece that knowledge together from cryptic, hand-written notes found on electrical panel labels; architectural drawings that may be inaccurate or out-of-date; and time-consuming, hands-on testing of electrical connections.
      </p>

      <p>
        Panel Spy addresses this problem by providing a dynamic, online model of your building’s electrical distribution network.
        It allows you to manage your entire inventory of panels, transformers, circuits, and devices, tracking their properties, connectivity, and locations.
        Most critically, Panel Spy allows you to update all of these features in real time.
      </p>

      <p>
        Panel Spy controls access to the model through user roles that reflect worker responsibility.
        An electrical intern, for example, could be assigned a role restricted to viewing and navigation, whereas a full-fledged technician could assume a role that allows alteration of the model and recording of contemporaneous notes about work being done.
      </p>

      <p>
        You can run Panel Spy in any modern browser, on a computer, smartphone or tablet.
      </p>

      <p>
        The Panel Spy team is proud to bring this innovative application to the world of building management.
        Your feedback is important to us.
        Please use the <a href="/?page=contact">Contact</a> page to convey your suggestions and comments.
      </p>

      <p>
        Thank you for using Panel Spy!
      </p>

      <br/>

      <dl class="dl-horizontal">
        <dt>Anil Navkal</dt>
        <dd>Lead</dd>
        <dt>Nikhil Navkal</dt>
        <dd>Developer, database</dd>
        <dt>Naomi Navkal</dt>
        <dd>Developer, user interface</dd>
      </dl>

    </div>

      <div class="hidden-xs col-sm-4 col-md-4 col-lg-5">
        <img id="label" src="about/label.jpg" class="img-responsive" style="max-width:250px; margin:auto;" alt="Panel Label">
        <img id="hover" src="about/hover.png" class="img-responsive" style="max-width:250px; margin:auto; display:none;" alt="Distribution">
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
