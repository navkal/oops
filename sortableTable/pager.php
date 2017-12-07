<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<style>
  .pager .glyphicon
  {
    font-size: 15px;
  }

  .pager .glyphicon:hover:not(.disabled)
  {
    cursor: pointer;
  }

  .pager .pagerButtons
  {
    margin-left: 15px;
    margin-right: 15px;
  }
</style>

<div class="pager" style="display:none" >

  <form class="form-inline">

    Page
    <select class="gotoPage form-control input-sm">
    </select>

    <span class="pagerButtons">
      <span class="glyphicon glyphicon-step-backward first" title="First" ></span>
      <span class="glyphicon glyphicon-backward prev" title="Previous" ></span>
      <span class="pagedisplay" id="<?=$sPageDisplayId?>"></span>
      <span class="glyphicon glyphicon-forward next" title="Next" ></span>
      <span class="glyphicon glyphicon-step-forward last" title="Last" ></span>
    </span>

    Page size
    <select class="pagesize form-control input-sm">
      <option value="10">10</option>
      <option value="20">20</option>
      <option value="30">30</option>
      <option value="40">40</option>
      <option value="all">All</option>
    </select>

  </form>

</div>
