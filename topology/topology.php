<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
  $iVersion = time();
?>

<script src="../topology/topology.js?version=<?=$iVersion?>"></script>

<style>
  svg
  {
    position: absolute;
    width: 98%;
    height: 75%;
  }

  #zoomButtons
  {
    position: fixed;
    margin-top: 8px;
    z-index: 100;
    border: 1px solid #0097cf;
    border-radius: 4px;
    background-color: #f0fbff;
  }

  .glyphicon
  {
    font-size: 11px;
  }
</style>

<div class="container-fluid">
  <div id="zoomButtons" >
    <span class="btn-group btn-group-xs" >
      <button class="btn btn-link btn-xs" onclick="zoomDiagram(true)" title="Zoom in">
        <span class="glyphicon glyphicon-plus" ></span>
      </button>
      <br/>
      <button class="btn btn-link btn-xs" onclick="zoomDiagram(false)" title="Zoom out">
        <span class="glyphicon glyphicon-minus" ></span>
      </button>
    </span>
  </div>
  <div class="clearfix" >
    <?php
      require_once $_SERVER["DOCUMENT_ROOT"]."/database/" . $_SESSION['panelSpy']['context']['enterprise'] . '/' . $_SESSION['panelSpy']['context']['facility'] . "/topology.svg";
    ?>
  </div>
</div>
