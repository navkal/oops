<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
  $iVersion = time();
?>

<!-- Utility scripts -->
<script src="/sortableTable/sortableTable.js?version=<?=$iVersion?>"></script>
<script src="/sortableTable/editSortableTableRow.js?version=<?=$iVersion?>"></script>
<script src="/sortableTable/removeSortableTableRow.js?version=<?=$iVersion?>"></script>
<link rel="stylesheet" href="/util/spinner.css?version=<?=$iVersion?>">
<link rel="stylesheet" href="/sortableTable/sortableTable.css?version=<?=$iVersion?>">

<!-- tablesorter Bootstrap theme -->
<link rel="stylesheet" href="/lib/tablesorter/theme.bootstrap_3.css">

<!-- tablesorter basic libraries -->
<script type="text/javascript" src="/lib/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="/lib/tablesorter/jquery.tablesorter.widgets.js"></script>


<style>
.tablesorter .tablesorter-filter-row .disabled
{
  display: none;
}
</style>

<div class="container-fluid">
  <div id="tableTop" style="padding-bottom:15px" >

    <!-- Title -->
    <span id="sortableTableTitle" class="h4"></span>

    <!-- Add button -->
    <button id="sortableTableAddButton" class="btn btn-default btn-sm pull-right" onclick="g_sAction='add'" data-toggle="modal" data-target="#editDialog" style="display:none" >
      <span class="glyphicon glyphicon-plus"></span> Add
    </button>
    <br/>

    <!-- Subtitle -->
    <small id="sortableTableSubtitle"></small>
  </div>

  <div id="spinner" class="spinner" >
  </div>

  <div id="content" class="panel panel-default">

    <table id="sortableTable" >

      <thead>
        <tr id="sortableTableHead">
        </tr>
      </thead>

      <tfoot>
        <tr id="sortableTableFoot">
        </tr>
      </tfoot>

      <tbody id="sortableTableBody" >
      </tbody>

    </table>

    <div id="sortableTableIsEmpty" class="panel-heading" style="display:none;text-align:center" >
      <h3 class="panel-title">
        This table is empty.
      </h3>
    </div>

  </div>
</div>

<div class="modal fade" id="sortableTableSuccessDialog" tabindex="-1" role="dialog" aria-labelledby="sortableTableSuccessLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title text-success" id="sortableTableSuccessLabel">Success</h4>
      </div>
      <div class="modal-body bg-success">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <p class="text-success" ><b>The table must be reloaded to render your changes.</b></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <form submit="return false;">
          <input type="text" id="dummyInput" style="width:0px; height:0px; opacity:0%; border:none">
          <button type="submit" class="btn btn-primary" data-dismiss="modal" >OK</button>
        </form>
      </div>
    </div>
  </div>
</div>
