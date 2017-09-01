# Copyright 2017 Panel Spy.  All rights reserved.

import sqlite3
import os
import time
import uuid
import dbCommon


conn = None
cur = None


def open_database( enterprise ):
    if enterprise != None:
        global conn
        global cur
        if conn == None:
            conn = sqlite3.connect('../database/' + enterprise + '/database.sqlite')
        if cur == None:
            cur = conn.cursor()


def make_device_label( name, room_id, facility ):

    # Get location details
    if room_id:
        cur.execute('''SELECT room_num, old_num, description FROM ''' + facility + '''Room WHERE id = ?''', (room_id,))
        rooms = cur.fetchone()
        location = rooms[0]
        location_old = rooms[1]
        location_descr = rooms[2]
    else:
        location = ''
        location_old = ''
        location_descr = ''

    # Generate label
    label = name

    if location or location_old or location_descr:
        label += ': <span class="glyphicon glyphicon-map-marker"></span>'

        if location:
            label += location + ' '
        if location_old:
            label += '(' + location_old + ') '
        if location_descr:
            label += "'" + location_descr + "'"

    label = label.strip()
    return label

def make_cirobj_label( o ):
    label = ''

    if o.object_type.lower() == 'panel':

        # It's a panel.  Generate label.

        # Concatenate label fragments
        if o.source:
            label += ' <span class="glyphicon glyphicon-arrow-up"></span>' + o.source

        if o.voltage:
            label += ' <span class="glyphicon glyphicon-flash"></span>' + o.voltage

        if o.loc_new or o.loc_old or o.loc_descr:
            label += ' <span class="glyphicon glyphicon-map-marker"></span>'
            if o.loc_new:
                label += o.loc_new + ' '
            if o.loc_old:
                label += '(' + o.loc_old + ') '
            if o.loc_descr:
                label += " '" + o.loc_descr + "'"

        # Prepend name
        name = o.path.split( '.' )[-1]
        label = label.strip()
        if label:
            label = name + ':' + label
        else:
            label = name

    else:

        # Not a panel; use description field from database
        label = o.description

    return label


def facility_names_to_ids( name_csv ):

    if name_csv != '':

        name_list = name_csv.split( ',' )

        facility_ids = []
        for i in range( len ( name_list ) ):
            cur.execute( 'SELECT id FROM Facility WHERE facility_name=?', ( name_list[i],) )
            id = cur.fetchone()[0]
            facility_ids.append( str( id ) )

        facility_id_csv = ','.join( facility_ids )

    else:

        facility_id_csv = ''

    return facility_id_csv


class device:
    def __init__(self,id=None,row=None,enterprise=None,facility=None,user_role=None):
        open_database( enterprise )

        if not row:
            cur.execute(
              '''SELECT *
                  FROM
                    (SELECT
                        ''' + facility + '''Device.id,
                        ''' + facility + '''Device.room_id,
                        ''' + facility + '''Device.parent_id,
                        ''' + facility + '''Device.description,
                        ''' + facility + '''Device.name,
                        ''' + facility + '''CircuitObject.path,
                        ''' + facility + '''CircuitObject.id
                    FROM ''' + facility + '''Device
                        LEFT JOIN ''' + facility + '''CircuitObject ON ''' + facility + '''Device.parent_id = ''' + facility + '''CircuitObject.id)
                  WHERE
                      id = ?''', (id,) )

            row = cur.fetchone()

        self.id = row[0]
        self.room_id = row[1]
        self.parent_id = row[2]
        self.description = row[3]
        self.name = row[4]
        self.parent_path = row[5] # For tree structure
        self.source_path = row[5] # For properties display and table
        self.label = make_device_label( self.name, self.room_id, facility )

        #gets room where device is located
        if str( self.room_id ).isdigit():
            cur.execute('SELECT * FROM ' + facility + 'Room WHERE id = ?', (self.room_id,))
            room = cur.fetchone()
            self.loc_new = room[1]
            self.loc_old = room[2]
            self.loc_type = room[3]
            self.loc_descr = room[4]
        else:
            self.loc_new = ''
            self.loc_old = ''
            self.loc_type = ''
            self.loc_descr = ''

        cur.execute( "SELECT timestamp, username, event_type, description FROM Activity WHERE target_table = '" + facility + "Device' AND target_column = 'id' AND target_value = ?", (self.id,) )
        self.events = cur.fetchall()

        if user_role == 'Technician':
            self.update_device = self.id


    def properties(self):
        print("room_id:", self.room_id)
        print("parent_id:",self.parent_id)
        print("description:", self.description)
        print("parent_path:",self.parent_path)
        print("loc_new:", self.loc_new)
        print("loc_old:", self.loc_old)

    def get_main_display(self):
        return {'ID': self.id,
                'Room ID': self.room_id,
                'Parent ID': self.parent_id,
                'Description':self.description,
                'Parent Path': self.parent_path,
                'Location New': self.loc_new,
                'Location Old': self.loc_old}


class cirobj:

    def __init__(self,id=None,path=None,getkids=True,enterprise=None,facility=None):
        open_database( enterprise )

        if id:
            cur.execute('SELECT * FROM ' + facility + 'CircuitObject WHERE id = ?', (id,))
        elif path:
            cur.execute('SELECT * FROM ' + facility + 'CircuitObject WHERE upper(path) = ?', (path.upper(),))
        else:
            cur.execute('SELECT * FROM ' + facility + 'CircuitObject WHERE path NOT LIKE "%.%"' )

        #initialize circuitObject properties
        row = cur.fetchone()
        cur.execute('SELECT * FROM Voltage WHERE id = ?',(row[4],))
        voltage = cur.fetchone()

        self.id = row[0]
        self.room_id = row[1]
        self.path = row[2]
        self.voltage = voltage[1]
        self.object_type = row[5].title()
        self.description = row[6]
        self.parent_id = row[7]
        self.source = row[10]

        # Retrieve parent path
        cur.execute('SELECT path FROM ' + facility + 'CircuitObject WHERE id = ?', (self.parent_id,))
        path_row = cur.fetchone()
        if path_row:
            self.parent_path = path_row[0]
        else:
            self.parent_path = ''

        # Get room information
        cur.execute('SELECT * FROM ' + facility + 'Room WHERE id = ?', (self.room_id,))
        room = cur.fetchone()
        self.loc_new = room[1]
        self.loc_old = room[2]
        self.loc_type = room[3]
        self.loc_descr = room[4]

        # Generate label
        self.label = make_cirobj_label( self )

        # Add image filename
        filename = '../database/' + enterprise + '/' + facility + '/images/' + self.path + '.jpg'
        if os.path.isfile( filename ):
            self.image_file = filename
        else:
            self.image_file = ''


        if getkids:

            # Retrieve children
            cur.execute('SELECT path FROM ' + facility + 'CircuitObject WHERE parent_id = ?', (self.id,))
            child_paths = cur.fetchall()
            self.children = []

            for i in range( len( child_paths ) ):
                child_path = child_paths[i][0]
                child = cirobj( path=child_path, getkids=False, enterprise=enterprise, facility=facility )
                filename = '../database/' + enterprise + '/' + facility + '/images/' + child_path + '.jpg'
                if os.path.isfile( filename ):
                    child.imagefile = filename
                else:
                    child.imagefile = ''
                self.children.append( [ child.id, child.path, child.label, child.object_type, child.imagefile ] )

            # Retrieve devices
            cur.execute(
              '''SELECT device_id
                    FROM
                        (SELECT
                            ''' + facility + '''Device.id AS device_id,
                            ''' + facility + '''Device.parent_id,
                            ''' + facility + '''CircuitObject.id,
                            ''' + facility + '''CircuitObject.path
                        FROM ''' + facility + '''Device
                            LEFT JOIN ''' + facility + '''CircuitObject ON ''' + facility + '''Device.parent_id = ''' + facility + '''CircuitObject.id)
                        WHERE path = ?''', (self.path,) )

            dev_ids = cur.fetchall()
            self.devices = []
            for i in range( len (dev_ids) ):
                dev_id = dev_ids[i][0]
                dev = device( id=dev_id, enterprise=enterprise, facility=facility )
                self.devices.append( [ dev.id, dev.loc_new, dev.loc_old, dev.loc_descr, dev.description, dev.label ] )


        cur.execute( "SELECT timestamp, username, event_type, description FROM Activity WHERE target_table = '" + facility + "CircuitObject' AND target_column = 'path' AND target_value = ?", (self.path,) )
        self.events = cur.fetchall()




    def get_main_display(self):
        return {'ID': self.id,
                'Room ID': self.room_id,
                'Path': self.path,
                'Voltage': self.voltage,
                'Type': self.object_type,
                'Description': self.description,
                'Parent Path': self.parent_path,
                'Children': self.children}


class search:
    def __init__(self, searchText, searchTargets=None, enterprise=None, facility=None):
        open_database( enterprise )

        if searchTargets:
            aTargets = searchTargets.split( ',' )
        else:
            aTargets = ['All']

        # Search CircuitObject paths
        if ( 'All' in aTargets ) or ( 'Path' in aTargets ):
            cur.execute('SELECT path, path FROM ' + facility + 'CircuitObject WHERE tail LIKE "%' + searchText + '%"')
            pathRows = cur.fetchall()
        else:
            pathRows = []


        # Search CircuitObject objects
        if ( 'All' in aTargets ) or ( 'Circuit' in aTargets ) or ( 'Panel' in aTargets ) or ( 'Transformer' in aTargets ):

            # Generate condition to select requested object types
            if ( 'All' in aTargets ) or ( ( 'Circuit' in aTargets ) and ( 'Panel' in aTargets ) and ( 'Transformer' in aTargets ) ):
                sWhere = ''
            else:
                aWhere = []
                if ( 'All' in aTargets ) or ( 'Circuit' in aTargets ):
                    aWhere.append( facility + 'CircuitObject.object_type = "Circuit"' )
                if ( 'All' in aTargets ) or ( 'Panel' in aTargets ):
                    aWhere.append( facility + 'CircuitObject.object_type = "Panel"' )
                if ( 'All' in aTargets ) or ( 'Transformer' in aTargets ):
                    aWhere.append( facility + 'CircuitObject.object_type = "Transformer"' )

                sWhere = 'WHERE '

                for i in range( len (aWhere) ):
                    sWhere += aWhere[i]
                    if i < ( len( aWhere ) - 1 ):
                        sWhere += ' OR '

            cur.execute(
              '''SELECT path, description
                  FROM
                    (SELECT
                        ''' + facility + '''CircuitObject.object_type,
                        ''' + facility + '''CircuitObject.description,
                        ''' + facility + '''CircuitObject.path,
                        ''' + facility + '''CircuitObject.tail AS tail,
                        ''' + facility + '''CircuitObject.search_text AS search_text,
                        ''' + facility + '''CircuitObject.source AS source,
                        Voltage.description AS voltage,
                        ''' + facility + '''Room.room_num AS location,
                        ''' + facility + '''Room.old_num AS location_old,
                        ''' + facility + '''Room.description AS location_descr
                    FROM ''' + facility + '''CircuitObject
                        LEFT JOIN Voltage ON ''' + facility + '''CircuitObject.voltage_id = Voltage.id
                        LEFT JOIN ''' + facility + '''Room ON ''' + facility + '''CircuitObject.room_id = ''' + facility + '''Room.id
                    '''
                    + sWhere +
                    ''')
                  WHERE
                      (object_type = "Panel"
                        AND
                        (tail LIKE "%''' + searchText + '''%"
                        OR source LIKE "%''' + searchText + '''%"
                        OR voltage LIKE "%''' + searchText + '''%"
                        OR location LIKE "%''' + searchText + '''%"
                        OR location_old LIKE "%''' + searchText + '''%"
                        OR location_descr LIKE "%''' + searchText + '''%"))
                      OR
                        search_text LIKE "%''' + searchText + '''%"''' )

            cirobjRows = cur.fetchall()
        else:
            cirobjRows = []


        # Search devices
        if ( 'All' in aTargets ) or ( 'Device' in aTargets ):
            cur.execute(
              '''SELECT path, description
                  FROM
                  (SELECT
                    ''' + facility + '''CircuitObject.path || "." || ''' + facility + '''Device.id AS path,
                    ''' + facility + '''Device.description,
                    ''' + facility + '''CircuitObject.id,
                    ''' + facility + '''Device.name as name,
                    ''' + facility + '''Room.room_num AS location,
                    ''' + facility + '''Room.old_num AS location_old,
                    ''' + facility + '''Room.description AS location_descr
                  FROM ''' + facility + '''Device
                    LEFT JOIN ''' + facility + '''CircuitObject ON ''' + facility + '''Device.parent_id = ''' + facility + '''CircuitObject.id
                    LEFT JOIN ''' + facility + '''Room ON ''' + facility + '''Device.room_id = ''' + facility + '''Room.id)
                  WHERE
                    name LIKE "%''' + searchText + '''%"
                    OR location LIKE "%''' + searchText + '''%"
                    OR location_old LIKE "%''' + searchText + '''%"
                    OR location_descr LIKE "%''' + searchText + '''%"''')

            devRows = cur.fetchall()
        else:
            devRows = []

        # Concatenate all search results
        self.searchResults = pathRows + cirobjRows + devRows


class sortableTable:
    def __init__(self, object_type, user_role, enterprise, facility):
        open_database( enterprise )

        if object_type == 'activity':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM Activity')
            objects = cur.fetchall()

            # Make table rows
            self.rows = []
            for obj in objects:
                target_table = obj[4]
                target_column = obj[5]
                target_value = obj[6]

                if ( target_table == facility + 'Device' ) and ( target_column == 'id' ):
                    # Target is in Device table.  Enhance text representing event target.
                    cur.execute('SELECT parent_id, name FROM ' + facility + 'Device WHERE id = ?', (target_value,))
                    device_row = cur.fetchone()
                    parent_id = device_row[0]
                    name = device_row[1]
                    cur.execute('SELECT path FROM ' + facility + 'CircuitObject WHERE id = ?', (parent_id,))
                    target_value = cur.fetchone()[0] + " '" + name + "'"

                facility_fullname = ''
                facility_id = obj[8]
                if facility_id:
                    cur.execute('SELECT facility_fullname FROM Facility WHERE id = ?', (facility_id,))
                    facility_fullname = cur.fetchone()[0]

                row = { 'timestamp': obj[1], 'event_trigger': obj[2], 'event_type': obj[3], 'facility_fullname': facility_fullname, 'event_target': target_value, 'event_description': obj[7] }
                self.rows.append( row )

        elif object_type == 'user':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM User')
            objects = cur.fetchall()

            # Make table rows
            self.rows = []
            for obj in objects:
                role_id = obj[3]
                if role_id:
                    cur.execute('SELECT role FROM Role WHERE id = ?', (role_id,))
                    role = cur.fetchone()[0]

                    username = obj[1]

                    if role == 'Administrator':
                        remove_username = ''
                    else:
                        remove_username = username

                    facilities = authFacilities( username, enterprise )
                    auth_facilities = '<br/>'.join( facilities.sorted_fullnames )
                    facilities_maps = facilities.__dict__

                    if obj[6]:
                        sStatus = 'Enabled'
                    else:
                        sStatus = 'Disabled'

                    row = { 'username': username, 'role': role, 'auth_facilities': auth_facilities, 'facilities_maps': facilities_maps, 'update_user': username, 'remove_user': remove_username, 'status': sStatus, 'first_name': obj[7], 'last_name': obj[8], 'email_address': obj[9], 'organization': obj[10], 'user_description': obj[4] }
                    self.rows.append( row )

        elif object_type == 'device':
            # Retrieve all objects of requested type
            cur.execute(
              '''SELECT *
                  FROM
                    (SELECT
                        ''' + facility + '''Device.id,
                        ''' + facility + '''Device.room_id,
                        ''' + facility + '''Device.parent_id,
                        ''' + facility + '''Device.description,
                        ''' + facility + '''Device.name,
                        ''' + facility + '''CircuitObject.path,
                        ''' + facility + '''CircuitObject.id
                    FROM ''' + facility + '''Device
                        LEFT JOIN ''' + facility + '''CircuitObject ON ''' + facility + '''Device.parent_id = ''' + facility + '''CircuitObject.id)''')



            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = device( row=obj, enterprise=enterprise, facility=facility, user_role=user_role )
                self.rows.append( row.__dict__ )

        elif object_type == 'location':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM ' + facility + 'Room')
            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = location( row=obj, facility=facility, user_role=user_role )
                self.rows.append( row.__dict__ )

        else:
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM ' + facility + 'CircuitObject WHERE upper(object_type) = ?', (object_type.upper(),))
            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = sortableTableRow( obj, enterprise, facility )
                self.rows.append( row.__dict__ )

        print('found ' + str(len(self.rows)) + ' rows' )


class location:
    def __init__(self, id=None, row=None, facility=None, user_role=None):
        if not row:
            cur.execute('SELECT * FROM ' + facility + 'Room WHERE id = ?', (id,))
            row = cur.fetchone()

        self.id = row[0]
        self.loc_new = row[1]
        self.loc_old = row[2]
        self.loc_type = row[3]
        self.loc_descr = row[4]

        cur.execute('SELECT COUNT(*) FROM ' + facility + 'Device WHERE room_id = ?', (self.id,))
        self.devices = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM ' + facility + 'CircuitObject WHERE room_id = ? AND object_type = "Panel"', (self.id,))
        self.panels = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM ' + facility + 'CircuitObject WHERE room_id = ? AND object_type = "Transformer"', (self.id,))
        self.transformers = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM ' + facility + 'CircuitObject WHERE room_id = ? AND object_type = "Circuit"', (self.id,))
        self.circuits = cur.fetchone()[0]

        if user_role == 'Technician':
            self.update_location = self.id


class sortableTableRow:

    def __init__(self,row, enterprise, facility ):

        self.id = row[0]
        self.room_id = row[1]
        self.path = row[2]
        self.object_type = row[5].title()
        self.parent_id = row[7]
        self.source = row[10]

        self.name = self.path.split('.')[-1]
        aName = self.name.split( '-', maxsplit=1 )
        if ( len( aName ) == 2 ) and aName[0].isdigit():
            self.name = aName[1]

        cur.execute('SELECT * FROM Voltage WHERE id = ?',(row[4],))
        voltage = cur.fetchone()
        self.voltage = voltage[1]

        cur.execute('SELECT * FROM ' + facility + 'Room WHERE id = ?', (self.room_id,))
        room = cur.fetchone()
        self.loc_new = room[1]
        self.loc_old = room[2]
        self.loc_type = room[3]
        self.loc_descr = room[4]

        # Add image filename
        filename = '../database/' + enterprise + '/' + facility + '/images/' + self.path + '.jpg'
        if os.path.isfile( filename ):
            self.image_file = self.path
        else:
            self.image_file = ''

        cur.execute('SELECT COUNT(id) FROM ' + facility + 'CircuitObject WHERE parent_id = ?', (self.id,))
        self.children = cur.fetchone()[0]

        cur.execute(
          '''SELECT COUNT( device_id )
                FROM
                    (SELECT
                        ''' + facility + '''Device.id AS device_id,
                        ''' + facility + '''Device.parent_id,
                        ''' + facility + '''CircuitObject.id,
                        ''' + facility + '''CircuitObject.path
                    FROM ''' + facility + '''Device
                        LEFT JOIN ''' + facility + '''CircuitObject ON ''' + facility + '''Device.parent_id = ''' + facility + '''CircuitObject.id)
                    WHERE path = ?''', (self.path,) )

        self.devices = cur.fetchone()[0]


class saveNotes:
    def __init__(self, args):

        open_database( args.enterprise )

        # Map facility name to facility ID
        cur.execute( 'SELECT id FROM Facility WHERE facility_name=?', ( args.facility,) )
        facility_id = cur.fetchone()[0]

        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), args.username, dbCommon.dcEventTypes['notes'], args.facility + args.targetTable, args.targetColumn, args.targetValue, args.notes, facility_id ) )

        conn.commit()

        self.status = 'success'


class signInUser:
    def __init__(self, username, password, enterprise=None):
        open_database( enterprise )

        self.username = username
        self.role = ''
        self.signInId = ''

        # Retrieve the user
        cur.execute('SELECT * FROM User WHERE lower(username) = ? AND password = ?', (username.lower(), dbCommon.hash(password),))
        user_row = cur.fetchone()

        # If we got a user row, load remaining user fields
        if user_row and user_row[6]:

            self.username = user_row[1]
            role_id = user_row[3]
            cur.execute('SELECT role FROM Role WHERE id = ?', (role_id,))
            self.role = cur.fetchone()[0]
            self.user_description = user_row[4]
            self.forceChangePassword = user_row[5]
            self.signInId = str( uuid.uuid1() )
            self.status = 'Enabled'
            self.first_name = user_row[7]
            self.last_name = user_row[8]
            self.email_address = user_row[9]
            self.organization = user_row[10]

            if enterprise != None:
                # Include enterprise fullname; PHP caller will move it to session context
                cur.execute( 'SELECT enterprise_fullname FROM Enterprise WHERE enterprise_name = ?', (enterprise,))
                self.enterprise_fullname = cur.fetchone()[0]


class changePassword:
    def __init__(self, by, username, oldPassword, password, enterprise=None):
        open_database( enterprise )

        self.username = username
        self.signInId = ''

        # Sign in with old password
        oldUser = signInUser( username, oldPassword )

        if oldUser.signInId:
            # Sign-in succeeded

            # Set the password and clear the force_change_password flag
            cur.execute( 'UPDATE User SET password=?, force_change_password=? WHERE lower(username)=?', ( dbCommon.hash(password), ( by != username ), username.lower() ) );
            conn.commit();

            # Sign in again with new password
            user = signInUser( username, password )

            if user.signInId:
                # Sign-in with new password succeeded
                self.username = user.username
                self.role = user.role
                self.user_description = user.user_description
                self.forceChangePassword = user.forceChangePassword
                self.signInId = user.signInId
                self.status = user.status
                self.first_name = user.first_name
                self.last_name = user.last_name
                self.email_address = user.email_address
                self.organization = user.organization


class addDevice:
    def __init__( self, by, parent_id, name, room_id, enterprise, facility ):
        open_database( enterprise )

        self.success = True


class updateDevice:
    def __init__( self, by, id, parent_id, name, room_id, enterprise, facility ):
        open_database( enterprise )

        self.success = True


class addLocation:
    def __init__( self, by, location, old_location, description, enterprise, facility ):
        open_database( enterprise )

        # Add new location
        target_table = facility + 'Room'
        cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (room_num, old_num, location_type, description)
            VALUES (?,?,?,?)''', (location, old_location, '', description) )

        # Log activity
        if location != '':
            target_column = 'room_num'
            target_value = location
        else:
            target_column = 'old_num'
            target_value = old_location

        cur.execute( 'SELECT id FROM Facility WHERE facility_name=?', ( facility,) )
        facility_id = cur.fetchone()[0]
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['addLocation'], target_table, target_column, target_value, "Add location ('" + location + "','" + old_location + "')", facility_id ) )

        conn.commit()

        self.success = True


class updateLocation:
    def __init__( self, by, id, location, old_location, description, enterprise, facility ):
        open_database( enterprise )

        # Update specified location
        target_table = facility + 'Room'

        cur.execute( '''UPDATE ''' + target_table + ''' SET room_num=?, old_num=?, description=? WHERE id=?''',
            ( location, old_location, description, id ) )

        # Log activity
        if location != '':
            target_column = 'room_num'
            target_value = location
        else:
            target_column = 'old_num'
            target_value = old_location

        cur.execute( 'SELECT id FROM Facility WHERE facility_name=?', ( facility,) )
        facility_id = cur.fetchone()[0]
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['updateLocation'], target_table, target_column, target_value, "Update location ('" + location + "','" + old_location + "')", facility_id ) )

        conn.commit()

        self.success = True


class addUser:
    def __init__(self, by, username, password, role, auth_facilities, status, first_name, last_name, email_address, organization, description, enterprise):
        open_database( enterprise )
        facility_id_csv = facility_names_to_ids( auth_facilities )
        self.unique = dbCommon.add_interactive_user( cur, conn, by, username, password, role, True, ( status == 'Enabled' ), first_name, last_name, email_address, organization, description, facility_id_csv )
        self.username = username


class updateUser:
    def __init__(self, by, username, oldPassword, password, role, auth_facilities, status, first_name, last_name, email_address, organization, description, enterprise):
        open_database( enterprise )

        self.messages = []

        if password != None:
            if oldPassword != None:
                # Authenticate credentials to change password
                user = changePassword( by, username, oldPassword, password )
                if user.signInId == '':
                    self.messages.append( 'Old Password not valid.' )
            else:
                # Change password without authentication
                cur.execute( 'UPDATE User SET password=?, force_change_password=? WHERE lower(username)=?', ( dbCommon.hash(password), ( by != username ), username.lower() ) );
                conn.commit()


        if len( self.messages ) == 0:
            cur.execute( 'SELECT id FROM Role WHERE role = ?', (role,))
            role_id = cur.fetchone()[0]
            facility_id_csv = facility_names_to_ids( auth_facilities )

            cur.execute( '''UPDATE User SET role_id=?, facility_ids=?, enabled=?, first_name=?, last_name=?, email_address=?, organization=?, description=? WHERE lower(username)=?''',
                ( role_id, facility_id_csv, ( status == 'Enabled' ), first_name, last_name, email_address, organization, description, username.lower() ) )

            cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description )
                VALUES (?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['updateUser'], 'User', 'username', username, "Update user '" + username + "'" ) )

            conn.commit()

            # Retrieve the user
            cur.execute('SELECT * FROM User WHERE lower(username) = ?', (username.lower(),))
            user_row = cur.fetchone()

            # If we got a user row, load remaining user fields
            if user_row:
                self.username = user_row[1]
                role_id = user_row[3]
                cur.execute('SELECT role FROM Role WHERE id = ?', (role_id,))
                self.role = cur.fetchone()[0]
                self.user_description = user_row[4]
                if user_row[6]:
                    self.status = 'Enabled'
                else:
                    self.status = 'Disabled'
                self.first_name = user_row[7]
                self.last_name = user_row[8]
                self.email_address = user_row[9]
                self.organization = user_row[10]


class removeUser:
    def __init__(self, by, username, enterprise):
        open_database( enterprise )
        self.username = username
        cur.execute( 'DELETE FROM User WHERE lower(username)=?', ( username.lower(), ) )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description )
            VALUES (?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['removeUser'], 'User', 'username', username, "Remove user '" + username + "'" ) )
        conn.commit()

class authFacilities:
    def __init__(self, username, enterprise):

        open_database( enterprise )

        names = []
        fullnames = []
        name_map = {}
        fullname_map = {}

        cur.execute('SELECT facility_ids FROM User WHERE username = ?', (username,))
        id_csv = cur.fetchone()[0]

        if id_csv != '':
            id_list = id_csv.split( ',' )

            for id in id_list:
                cur.execute('SELECT facility_name, facility_fullname FROM Facility WHERE id = ?', (id,))
                row = cur.fetchone()
                names.append( row[0] )
                fullnames.append( row[1] )
                name_map[ row[0] ] = row[1]
                fullname_map[ row[1] ] = row[0]

        self.sorted_names = sorted( names )
        self.sorted_fullnames = sorted( fullnames )
        self.name_map = name_map
        self.fullname_map = fullname_map

class allFacilities:
    def __init__(self, enterprise):

        open_database( enterprise )
        cur.execute('SELECT facility_name, facility_fullname FROM Facility')
        rows = cur.fetchall()


        names = []
        fullnames = []
        name_map = {}
        fullname_map = {}
        for row in rows:
            names.append( row[0] )
            fullnames.append( row[1] )
            name_map[ row[0] ] = row[1]
            fullname_map[ row[1] ] = row[0]

        self.sorted_names = sorted( names )
        self.sorted_fullnames = sorted( fullnames )
        self.name_map = name_map
        self.fullname_map = fullname_map


class deviceDropdowns:
    def __init__(self, enterprise, facility):

        open_database( enterprise )

        # Get all potential sources
        cur.execute('SELECT id, path FROM ' + facility + 'CircuitObject WHERE object_type = "Circuit"')
        rows = cur.fetchall()

        sources = []
        for row in rows:
            source = { 'parent_id': row[0], 'source_path': row[1] }
            sources.append( source )

        self.sources = sources

        # Get all locations
        cur.execute('SELECT * FROM ' + facility + 'Room')
        rows = cur.fetchall()

        locations = []
        for row in rows:
            location = { 'room_id': row[0], 'loc_new': row[1], 'loc_old': row[2], 'loc_descr': row[4] }
            locations.append( location )

        self.locations = locations
