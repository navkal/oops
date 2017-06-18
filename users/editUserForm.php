<!-- Copyright 2017 Panel Spy.  All rights reserved. -->

<div class="row">
  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <form id="editUserForm" onsubmit="onSubmitUser(event); return false;" >
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" id="username" maxlength="<?=MAX_USERNAME_LENGTH+1?>" placeholder="Username" autocomplete="off" >
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="password" placeholder="Password" >
      </div>
      <div class="form-group">
        <label for="confirm" >Confirm</label>
        <input type="password" maxlength="<?=MAX_PASSWORD_LENGTH+1?>" class="form-control" id="confirm" placeholder="Confirm Password" >
      </div>
      <div class="form-group">
        <label id="roleLabel" >Role</label>
        <select id="role" class="form-control">
          <option value="Visitor">Visitor</option>
          <option value="Technician">Technician</option>
        </select>
        <input type="text" id="readonlyRole" class="form-control" readonly >
      </div>
      <div class="form-group">
        <label for="description" >Description</label>
        <textarea class="form-control" id="description" ></textarea>
      </div>
    </form>
  </div>
</div>
