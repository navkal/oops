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
      <div class="settingsHide" >
        <div class="form-group">
          <label for="status" >Status</label>
          <select id="status" class="form-control">
            <option value="Enabled">Enabled</option>
            <option value="Disabled">Disabled</option>
          </select>
        </div>
      </div>
      <div class="settingsReadonly" >
        <div class="form-group">
          <label for="first_name" >First Name</label>
          <input type="text" class="form-control" id="first_name" maxlength="40" placeholder="First Name" autocomplete="off" >
        </div>
        <div class="form-group">
          <label for="last_name" >Last Name</label>
          <input type="text" class="form-control" id="last_name" maxlength="40" placeholder="Last Name" autocomplete="off" >
        </div>
        <div class="form-group">
          <label for="email_address" >Email Address</label>
          <input type="text" class="form-control" id="email_address" maxlength="100" placeholder="Email Address" autocomplete="off" >
        </div>
        <div class="form-group">
          <label for="organization" >Organization</label>
          <input type="text" class="form-control" id="organization" maxlength="100" placeholder="Organization" autocomplete="off" >
        </div>
      </div>
      <div class="settingsHide" >
        <div class="form-group">
          <label for="user_description" >Description</label>
          <textarea class="form-control" id="user_description" ></textarea>
        </div>
      </div>
    </form>
  </div>
</div>
