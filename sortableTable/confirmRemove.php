<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/util/security.php';
?>

<!-- Confirm Remove dialog -->
<div class="modal fade" id="removeDialog" tabindex="-1" role="dialog" aria-labelledby="removeObjectLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><span class="glyphicon glyphicon-remove"></span></span></button>
        <h4 class="modal-title" id="removeObjectLabel"></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <form id="removeDialogForm" onsubmit="removeObject(event); return false;" >
              <div class="form-group" id="removeWhatDiv" >
                <label id="removeWhatLabel" for="removeWhat"></label>
                <input type="text" class="form-control" id="removeWhat" disabled >
              </div>
              <div class="form-group" id="removeCommentDiv" hidden >
                <label for="remove_comment"></label>
                <textarea id="remove_comment" class="form-control" maxlength="200" ></textarea>
              </div>
              <button type="submit" id="removeFormSubmitButton" style="display:none" ></button>
            </form>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <div style="text-align:center;" >
          <button type="button" id="removeFormSubmitProxy" class="btn btn-danger" onclick="$('#removeFormSubmitButton').click()" ></button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>
