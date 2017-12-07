<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<div class="pager" style="display:none" >
  <form class="form-inline">
    Page <select class="gotoPage form-control input-sm"></select>
    <span class="glyphicon glyphicon-step-backward first" ></span>
    <span class="glyphicon glyphicon-backward prev" ></span>
    <span class="pagedisplay" id="<?=$sPageDisplayId?>"></span>
    <span class="glyphicon glyphicon-forward next" ></span>
    <span class="glyphicon glyphicon-step-forward last" ></span>
    <select class="pagesize form-control input-sm">
      <option value="10">10</option>
      <option value="20">20</option>
      <option value="30">30</option>
      <option value="40">40</option>
      <option value="all">All Rows</option>
    </select>
  </form>
</div>
