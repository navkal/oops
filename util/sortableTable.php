<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  $iVersion = time();
?>

<!-- Utility scripts -->
<script src="util/sortableTable.js?version=<?=$iVersion?>"></script>
<link rel="stylesheet" href="util/spinner.css?version=<?=$iVersion?>">

<!-- tablesorter Bootstrap theme -->
<link rel="stylesheet" href="../lib/tablesorter/theme.bootstrap_3.css">

<!-- tablesorter basic libraries -->
<script type="text/javascript" src="../lib/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="../lib/tablesorter/jquery.tablesorter.widgets.js"></script>


<style>
.tablesorter .tablesorter-filter-row .disabled
{
  display: none;
}
</style>

<div class="container-fluid">
  <h4 id="sortableTableTitle"></h4>

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

  </div>
</div>
