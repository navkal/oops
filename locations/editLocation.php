<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<?php
  require_once $_SERVER["DOCUMENT_ROOT"]."/util/security.php";
?>

<!-- Add Location button -->
<button id="addLocationButton" class="btn btn-default btn-sm pull-right" onclick="alert('add location')" data-toggle="modal" data-target="#editLocationDialog" data-backdrop="static" data-keyboard=false style="display:none" >
  <span class="glyphicon glyphicon-plus"></span> Add Location
</button>

<!-- Edit Location dialog -->
<div class="modal fade" id="editLocationDialog" tabindex="-1" role="dialog" aria-labelledby="editLocationLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="editLocationLabel"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="editLocationForm" class="form-horizontal" onsubmit="onSubmitLocation(event); return false;" >
              <div class="form-group">
                <label for="moooooooooooo"></label>
                <div>
                  <input type="text" class="form-control" id="moooooooooooo" maxlength="000000001" value="THIS IS A STUB">
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div style="text-align:center;" >
          <button id="submit" type="submit" class="btn btn-primary" form="editLocationForm" >Do Something</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
        <br/>
        <div id="messages" class="alert alert-danger" style="text-align:left; display:none" role="alert">
          <ul id="messageList">
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

