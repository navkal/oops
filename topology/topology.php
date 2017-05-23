<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<script src="topology/topology.js?version=<?=time()?>"></script>

<div class="container">
  <div id="headerContent" class="page-header" >
    <div class="row" >
      <div class="col-xs-12 col-sm-3 col-md-2 col-lg-2 headerButton" >
        <a href="topology/circuitTopology.pdf" target="_blank" title="Open Circuit Diagram in a new page" >
          <span class="glyphicon glyphicon-blackboard"></span> Circuit Diagram
        </a>
      </div>
      <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2 headerButton" >
        <a href="topology/tree.html" target="_blank" title="Open Circuit Tree graphic in a new page" >
          <span class="glyphicon glyphicon-blackboard"></span> Circuit Tree
        </a>
      </div>
      <div class="col-xs-12 col-sm-7 col-md-8 col-lg-8 headerButton" >
        <a class="btn btn-default btn-sm" href="topology/downloadTree.php" onclick="return startTreeDump(event);" title="Generate and download dump of Circuit Tree" ><span class="glyphicon glyphicon-download-alt"></span> Tree Download <sup><span class="glyphicon glyphicon-asterisk"></span></sup></a>
        <span class="well-sm text-info" style="white-space:nowrap;" >
          <small>
            <sup>
              <span class="glyphicon glyphicon-asterisk"></span>
            </sup>
            Takes several minutes
          </small>
        </span>
      </div>
    </div>
  </div>
  <div id="dumpStatus" class="well well-sm hidden" >
    <span class="glyphicon glyphicon-hourglass" style="font-size:18px;" ></span> Generating tree. <span id="dumpTime"></span>
  </div>

</div>
