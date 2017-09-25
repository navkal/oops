# Copyright 2017 Panel Spy.  All rights reserved.

import sqlite3
import os
import shutil
import time
import uuid
import natsort
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
        cur.execute('''SELECT room_num, old_num, description FROM ''' + facility + '''_Room WHERE id = ?''', (room_id,))
        room = cur.fetchone()
        location = room[0]
        location_old = room[1]
        location_descr = room[2]
    else:
        location = ''
        location_old = ''
        location_descr = ''

    # Generate label
    label = name

    if location or location_old or location_descr:
        label += ': <span class="glyphicon glyphicon-map-marker"></span>'
        label += dbCommon.format_location( location, location_old, location_descr )

    label = label.strip()
    return label


def make_cirobj_label( o ):
    label = ''

    if o.object_type in ( 'Panel', 'Transformer' ):

        # Concatenate label fragments
        if o.source:
            label += ' <span class="glyphicon glyphicon-arrow-up"></span>' + o.source

        if o.voltage:
            label += ' <span class="glyphicon glyphicon-flash"></span>' + o.voltage

        if o.loc_new or o.loc_old or o.loc_descr:
            label += ' <span class="glyphicon glyphicon-map-marker"></span>'
            label += dbCommon.format_location( o.loc_new, o.loc_old, o.loc_descr );

    else:
        # Circuit - show description
        label = o.description

    # Prepend name
    name = o.path.split( '.' )[-1]
    label = label.strip()

    if label:
        label = name + ': ' + label
    else:
        label = name

    return label


def facility_name_to_id( facility_name ):
    cur.execute( 'SELECT id FROM Facility WHERE facility_name=?', ( facility_name,) )
    facility_id = cur.fetchone()[0]
    return str( facility_id )


def facility_names_to_ids( name_csv ):

    if name_csv != '':

        name_list = name_csv.split( ',' )

        facility_ids = []
        for i in range( len ( name_list ) ):
            facility_ids.append( facility_name_to_id( name_list[i] ) )

        facility_id_csv = ','.join( facility_ids )

    else:

        facility_id_csv = ''

    return facility_id_csv


def get_location_dropdown( facility ):

    cur.execute('SELECT * FROM ' + facility + '_Room')
    rows = cur.fetchall()

    locations = []
    for row in rows:
        location = { 'id': row[0], 'text': dbCommon.format_location( row[1], row[2], row[4] )  }
        locations.append( location )

    return natsort.natsorted( locations, key=lambda x: x['text'] )


def get_circuit_object_dropdown( facility, sTypes ):

    cur.execute('SELECT id, path, voltage_id, object_type FROM ' + facility + '_CircuitObject WHERE object_type IN (' + sTypes + ')'  )
    rows = cur.fetchall()

    testId = 0
    objects = []
    for row in rows:
        testId = max( testId, row[0] )
        objects.append( { 'id': row[0], 'text': row[1], 'voltage_id': row[2], 'object_type': row[3] } )

    # To test large volume of dropdown elements, change 0 to number desired.
    for i in range( len(objects), 0 ):
        objects.append( { 'id': str( testId + i ), 'text': str( testId + i ) } )

    return natsort.natsorted( objects, key=lambda x: x['text'] )


def test_path_availability( target_table, parent_id, tail ):

    # Get parent path
    cur.execute('SELECT path FROM ' + target_table + ' WHERE id = ?', (parent_id,))
    parent_path = cur.fetchone()[0]

    # Format test path
    path = parent_path + '.' + tail

    # Attempt to get test id
    cur.execute('SELECT id FROM ' + target_table + ' WHERE path = ?', (path,))
    test_row = cur.fetchone()

    if test_row:
        test_id = str( test_row[0] )
    else:
        test_id = None

    # Return results
    source  = parent_path.split( '.' )[-1]
    return ( test_id, path, source )


class device:
    def __init__(self,id=None,row=None,enterprise=None,facility=None,user_role=None):
        open_database( enterprise )

        if not row:
            cur.execute(
              '''SELECT *
                  FROM
                    (SELECT
                        ''' + facility + '''_Device.id,
                        ''' + facility + '''_Device.room_id,
                        ''' + facility + '''_Device.parent_id,
                        ''' + facility + '''_Device.description,
                        ''' + facility + '''_Device.name,
                        ''' + facility + '''_CircuitObject.path,
                        ''' + facility + '''_CircuitObject.id
                    FROM ''' + facility + '''_Device
                        LEFT JOIN ''' + facility + '''_CircuitObject ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_CircuitObject.id)
                  WHERE
                      id = ?''', (id,) )

            row = cur.fetchone()

        self.object_type = "Device"
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
            cur.execute('SELECT * FROM ' + facility + '_Room WHERE id = ?', (self.room_id,))
            room = cur.fetchone()
            self.loc_new = room[1]
            self.loc_old = room[2]
            self.loc_type = room[3]
            self.loc_descr = room[4]
            formatted_location = dbCommon.format_location( self.loc_new, self.loc_old, self.loc_descr )
        else:
            self.loc_new = ''
            self.loc_old = ''
            self.loc_type = ''
            self.loc_descr = ''
            formatted_location = ''

        cur.execute( "SELECT timestamp, username, event_type, description FROM Activity WHERE target_table = '" + facility + "_Device' AND target_column = 'id' AND target_value = ?", (self.id,) )
        self.events = cur.fetchall()

        if user_role == 'Technician':
            self.update_device = self.id
            self.remove_device = self.id
            self.remove_what = 'name'
            self.formatted_location = formatted_location


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
            cur.execute('SELECT * FROM ' + facility + '_CircuitObject WHERE id = ?', (id,))
        elif path:
            cur.execute('SELECT * FROM ' + facility + '_CircuitObject WHERE upper(path) = ?', (path.upper(),))
        else:
            cur.execute('SELECT * FROM ' + facility + '_CircuitObject WHERE path NOT LIKE "%.%"' )

        #initialize circuitObject properties
        row = cur.fetchone()
        cur.execute('SELECT * FROM Voltage WHERE id = ?',(row[4],))
        voltage = cur.fetchone()

        self.id = str( row[0] )
        self.room_id = row[1]
        self.path = row[2]
        self.voltage = voltage[1]
        self.object_type = row[5].title()
        self.description = row[6]
        self.parent_id = row[7]
        self.source = row[10]

        if self.object_type == 'Circuit':
            self.circuit_descr = self.description
        elif self.object_type == 'Panel':
            self.panel_descr = self.description
        elif self.object_type == 'Transformer':
            self.transformer_descr = self.description

        # Retrieve parent path
        cur.execute('SELECT path FROM ' + facility + '_CircuitObject WHERE id = ?', (self.parent_id,))
        path_row = cur.fetchone()
        if path_row:
            self.parent_path = path_row[0]
        else:
            self.parent_path = ''

        # Get room information
        cur.execute('SELECT * FROM ' + facility + '_Room WHERE id = ?', (self.room_id,))
        room = cur.fetchone()
        self.loc_new = room[1]
        self.loc_old = room[2]
        self.loc_type = room[3]
        self.loc_descr = room[4]

        # Generate label
        self.label = make_cirobj_label( self )

        # Add image filename
        filename = '../database/' + enterprise + '/' + facility + '/images/' + self.id + '.jpg'
        if os.path.isfile( filename ):
            self.image_file = filename
        else:
            self.image_file = ''


        if getkids:

            # Retrieve children
            cur.execute('SELECT id, path FROM ' + facility + '_CircuitObject WHERE parent_id = ?', (self.id,))
            child_paths = cur.fetchall()
            self.children = []

            for i in range( len( child_paths ) ):
                child_id = str( child_paths[i][0] )
                child_path = child_paths[i][1]
                child = cirobj( path=child_path, getkids=False, enterprise=enterprise, facility=facility )
                filename = '../database/' + enterprise + '/' + facility + '/images/' + child_id + '.jpg'
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
                            ''' + facility + '''_Device.id AS device_id,
                            ''' + facility + '''_Device.parent_id,
                            ''' + facility + '''_CircuitObject.id,
                            ''' + facility + '''_CircuitObject.path
                        FROM ''' + facility + '''_Device
                            LEFT JOIN ''' + facility + '''_CircuitObject ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_CircuitObject.id)
                        WHERE path = ?''', (self.path,) )

            dev_ids = cur.fetchall()
            self.devices = []
            for i in range( len (dev_ids) ):
                dev_id = dev_ids[i][0]
                dev = device( id=dev_id, enterprise=enterprise, facility=facility )
                self.devices.append( [ dev.id, dev.loc_new, dev.loc_old, dev.loc_descr, dev.description, dev.label ] )


        cur.execute( "SELECT timestamp, username, event_type, description FROM Activity WHERE target_table = '" + facility + "_CircuitObject' AND target_column = 'path' AND target_value = ?", (self.path,) )
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
            cur.execute('SELECT path, path FROM ' + facility + '_CircuitObject WHERE tail LIKE "%' + searchText + '%"')
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
                    aWhere.append( facility + '_CircuitObject.object_type = "Circuit"' )
                if ( 'All' in aTargets ) or ( 'Panel' in aTargets ):
                    aWhere.append( facility + '_CircuitObject.object_type = "Panel"' )
                if ( 'All' in aTargets ) or ( 'Transformer' in aTargets ):
                    aWhere.append( facility + '_CircuitObject.object_type = "Transformer"' )

                sWhere = 'WHERE '

                for i in range( len (aWhere) ):
                    sWhere += aWhere[i]
                    if i < ( len( aWhere ) - 1 ):
                        sWhere += ' OR '

            cur.execute(
              '''SELECT path, search_result
                  FROM
                    (SELECT
                        ''' + facility + '''_CircuitObject.path,
                        ''' + facility + '''_CircuitObject.search_result,
                        ''' + facility + '''_CircuitObject.object_type,
                        ''' + facility + '''_CircuitObject.tail AS tail,
                        ''' + facility + '''_CircuitObject.source AS source,
                        ''' + facility + '''_CircuitObject.description AS description,
                        Voltage.description AS voltage,
                        ''' + facility + '''_Room.room_num AS location,
                        ''' + facility + '''_Room.old_num AS location_old,
                        ''' + facility + '''_Room.description AS location_descr
                    FROM ''' + facility + '''_CircuitObject
                        LEFT JOIN Voltage ON ''' + facility + '''_CircuitObject.voltage_id = Voltage.id
                        LEFT JOIN ''' + facility + '''_Room ON ''' + facility + '''_CircuitObject.room_id = ''' + facility + '''_Room.id
                    '''
                    + sWhere +
                    ''')
                  WHERE
                      tail LIKE "%''' + searchText + '''%"
                      OR source LIKE "%''' + searchText + '''%"
                      OR voltage LIKE "%''' + searchText + '''%"
                      OR location LIKE "%''' + searchText + '''%"
                      OR location_old LIKE "%''' + searchText + '''%"
                      OR location_descr LIKE "%''' + searchText + '''%"
                      OR description LIKE "%''' + searchText + '''%"''' )

            cirobjRows = cur.fetchall()
        else:
            cirobjRows = []


        # Search devices
        if ( 'All' in aTargets ) or ( 'Device' in aTargets ):
            cur.execute(
              '''SELECT path, description
                  FROM
                  (SELECT
                    ''' + facility + '''_CircuitObject.path || "." || ''' + facility + '''_Device.id AS path,
                    ''' + facility + '''_Device.description,
                    ''' + facility + '''_CircuitObject.id,
                    ''' + facility + '''_Device.name AS name,
                    ''' + facility + '''_Room.room_num AS location,
                    ''' + facility + '''_Room.old_num AS location_old,
                    ''' + facility + '''_Room.description AS location_descr
                  FROM ''' + facility + '''_Device
                    LEFT JOIN ''' + facility + '''_CircuitObject ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_CircuitObject.id
                    LEFT JOIN ''' + facility + '''_Room ON ''' + facility + '''_Device.room_id = ''' + facility + '''_Room.id)
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

        if object_type == 'recycle':
            recycle_table = facility + '_Recycle'
            cur.execute( 'SELECT * FROM ' + recycle_table )
            objects = cur.fetchall()
            self.rows = []

            for obj in objects:

                remove_object_type = obj[2]
                remove_object_id = obj[5]

                if remove_object_type == 'Device':
                    cur.execute('SELECT * FROM ' + facility + '_Removed_Device WHERE id = ?', (remove_object_id,))
                    device_row = cur.fetchone()
                    room_id = device_row[1]
                    parent_id = device_row[2]
                    name = device_row[5]

                    fields = { 'name': name, 'source_path': parent_id, 'room_id': room_id }

                elif remove_object_type == 'Location':
                    cur.execute('SELECT * FROM ' + facility + '_Removed_Room WHERE id = ?', (remove_object_id,))
                    room = cur.fetchone()
                    fields = { 'loc_new': room[1], 'loc_old': room[2], 'loc_descr': room[4] }

                row = { 'id': obj[0], 'timestamp': obj[1], 'remove_object_type': remove_object_type, 'remove_object_origin': obj[3], 'remove_comment': obj[4], 'remove_object_id': remove_object_id, 'restore_object': obj[0], 'fields': fields }
                self.rows.append( row )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['timestamp'], reverse=True )

        elif object_type == 'activity':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM Activity')
            objects = cur.fetchall()

            # Make table rows
            self.rows = []
            for obj in objects:
                target_table = obj[4]
                target_column = obj[5]
                target_value = obj[6]

                if ( target_table == facility + '_Device' ) and ( target_column == 'id' ):
                    # Target is in Device table.  Enhance text representing event target.
                    cur.execute('SELECT parent_id, name FROM ' + facility + '_Device WHERE id = ?', (target_value,))
                    device_row = cur.fetchone()
                    parent_id = device_row[0]
                    name = device_row[1]
                    cur.execute('SELECT path FROM ' + facility + '_CircuitObject WHERE id = ?', (parent_id,))
                    target_value = cur.fetchone()[0] + " '" + name + "'"

                facility_fullname = ''
                facility_id = obj[8]
                if facility_id:
                    cur.execute('SELECT facility_fullname FROM Facility WHERE id = ?', (facility_id,))
                    facility_fullname = cur.fetchone()[0]

                row = { 'timestamp': obj[1], 'event_trigger': obj[2], 'event_type': obj[3], 'facility_fullname': facility_fullname, 'event_target': target_value, 'event_description': obj[7] }
                self.rows.append( row )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['timestamp'], reverse=True )

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

                    id = obj[0]
                    username = obj[1]

                    if role == 'Administrator':
                        remove_id = ''
                        remove_what = ''
                    else:
                        remove_id = id
                        remove_what = 'username'

                    facilities = authFacilities( username, enterprise )
                    auth_facilities = '<br/>'.join( facilities.sorted_fullnames )
                    facilities_maps = facilities.__dict__

                    if obj[6]:
                        sStatus = 'Enabled'
                    else:
                        sStatus = 'Disabled'

                    row = { 'id': id, 'username': username, 'role': role, 'auth_facilities': auth_facilities, 'facilities_maps': facilities_maps, 'update_user': username, 'remove_user': remove_id, 'remove_what': remove_what, 'status': sStatus, 'first_name': obj[7], 'last_name': obj[8], 'email_address': obj[9], 'organization': obj[10], 'user_description': obj[4] }
                    self.rows.append( row )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['username'] )

        elif object_type == 'device':
            # Retrieve all objects of requested type
            cur.execute(
              '''SELECT *
                  FROM
                    (SELECT
                        ''' + facility + '''_Device.id,
                        ''' + facility + '''_Device.room_id,
                        ''' + facility + '''_Device.parent_id,
                        ''' + facility + '''_Device.description,
                        ''' + facility + '''_Device.name,
                        ''' + facility + '''_CircuitObject.path,
                        ''' + facility + '''_CircuitObject.id
                    FROM ''' + facility + '''_Device
                        LEFT JOIN ''' + facility + '''_CircuitObject ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_CircuitObject.id)''')



            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = device( row=obj, enterprise=enterprise, facility=facility, user_role=user_role )
                self.rows.append( row.__dict__ )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['source_path'] )

        elif object_type == 'location':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM ' + facility + '_Room')
            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = location( row=obj, facility=facility, user_role=user_role )
                self.rows.append( row.__dict__ )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['loc_new'] )

        else:
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM ' + facility + '_CircuitObject WHERE upper(object_type) = ?', (object_type.upper(),))
            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = sortableTableRow( obj, user_role, enterprise, facility )
                self.rows.append( row.__dict__ )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['path'] )

        print('found ' + str(len(self.rows)) + ' rows' )


class location:
    def __init__(self, id=None, row=None, facility=None, user_role=None):
        if not row:
            cur.execute('SELECT * FROM ' + facility + '_Room WHERE id = ?', (id,))
            row = cur.fetchone()

        self.id = row[0]
        self.loc_new = row[1]
        self.loc_old = row[2]
        self.loc_type = row[3]
        self.loc_descr = row[4]

        cur.execute('SELECT COUNT(*) FROM ' + facility + '_Device WHERE room_id = ?', (self.id,))
        self.devices = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM ' + facility + '_CircuitObject WHERE room_id = ? AND object_type = "Panel"', (self.id,))
        self.panels = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM ' + facility + '_CircuitObject WHERE room_id = ? AND object_type = "Transformer"', (self.id,))
        self.transformers = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM ' + facility + '_CircuitObject WHERE room_id = ? AND object_type = "Circuit"', (self.id,))
        self.circuits = cur.fetchone()[0]

        if user_role == 'Technician':
            self.update_location = self.id
            self.formatted_location = dbCommon.format_location( self.loc_new, self.loc_old, self.loc_descr )
            self.remove_what = 'formatted_location'
            if ( self.devices + self.panels + self.transformers + self.circuits ) == 0:
                self.remove_location = self.id
            else:
                self.remove_location = ''


class sortableTableRow:

    def __init__( self, row, user_role, enterprise, facility ):

        self.id = str( row[0] )
        self.room_id = row[1]
        self.path = row[2]
        self.object_type = row[5].title()
        self.description = row[6]
        self.parent_id = row[7]
        self.source = row[10]

        # Extract number and name from path tail
        tail = self.path.split('.')[-1]
        aTail = tail.split( '-', maxsplit=1 )

        self.number = ''
        self.name = ''

        if aTail[0].isdigit():
            # Segment format is <number> or <number>-<name>.  Save the number.
            self.number = aTail[0]
            if len( aTail ) == 2:
                # Segment format is <number>-<name>.  Save the name.
                self.name = aTail[1]
        else:
            # Segment format is <notNumber>.  Save as name.
            self.name = tail

        cur.execute('SELECT * FROM Voltage WHERE id = ?',(row[4],))
        voltage = cur.fetchone()
        self.voltage_id = voltage[0]
        self.voltage = voltage[1]

        cur.execute('SELECT * FROM ' + facility + '_Room WHERE id = ?', (self.room_id,))
        room = cur.fetchone()
        self.loc_new = room[1]
        self.loc_old = room[2]
        self.loc_type = room[3]
        self.loc_descr = room[4]

        # Add image filename
        filename = '../database/' + enterprise + '/' + facility + '/images/' + self.id + '.jpg'
        if os.path.isfile( filename ):
            self.image_file = self.path
        else:
            self.image_file = ''

        cur.execute('SELECT COUNT(id) FROM ' + facility + '_CircuitObject WHERE parent_id = ?', (self.id,))
        self.children = cur.fetchone()[0]

        cur.execute(
          '''SELECT COUNT( device_id )
                FROM
                    (SELECT
                        ''' + facility + '''_Device.id AS device_id,
                        ''' + facility + '''_Device.parent_id,
                        ''' + facility + '''_CircuitObject.id,
                        ''' + facility + '''_CircuitObject.path
                    FROM ''' + facility + '''_Device
                        LEFT JOIN ''' + facility + '''_CircuitObject ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_CircuitObject.id)
                    WHERE path = ?''', (self.path,) )

        self.devices = cur.fetchone()[0]

        if self.object_type == 'Circuit':
            self.circuit_descr = self.description
        elif self.object_type == 'Panel':
            self.panel_descr = self.description
        elif self.object_type == 'Transformer':
            self.transformer_descr = self.description

        if user_role == 'Technician':
            if self.object_type == 'Circuit':
                self.update_circuit = self.id
            elif self.object_type == 'Panel':
                self.update_panel = self.id
            elif self.object_type == 'Transformer':
                self.update_transformer = self.id


class saveNotes:
    def __init__(self, args):

        open_database( args.enterprise )

        # Map facility name to facility ID
        facility_id = facility_name_to_id( args.facility );

        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), args.username, dbCommon.dcEventTypes['notes'], args.facility + '_' + args.targetTable, args.targetColumn, args.targetValue, args.notes, facility_id ) )

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


class addCircuitObject:
    def __init__( self, by, object_type, parent_id, tail, voltage_id, room_id, description, filename, enterprise, facility ):
        open_database( enterprise )

        self.messages = []
        target_table = facility + '_CircuitObject'

        # Determine whether path is available
        ( test_id, path, source ) = test_path_availability( target_table, parent_id, tail )

        if test_id:
            # Path already in use
            self.messages.append( "Path '" + path + "' is not available." )

        else:
            # Path is not in use; okay to add

            # Get fragments of search result text
            cur.execute('SELECT description FROM Voltage WHERE id = ?',(voltage_id,))
            voltage = cur.fetchone()[0]

            cur.execute('''SELECT room_num, old_num, description FROM ''' + facility + '''_Room WHERE id = ?''', (room_id,))
            loc = cur.fetchone()
            location = loc[0]
            location_old = loc[1]
            location_descr = loc[2]

            # Generate search result text
            search_result = dbCommon.make_search_result( source, voltage, location, location_old, location_descr, object_type, description, tail )

            # Add new object
            cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (room_id, path, zone, voltage_id, object_type, description, parent_id, tail, search_result, source)
                 VALUES (?,?,?,?,?,?,?,?,?,?)''', (room_id, path, '', voltage_id, object_type, description, parent_id, tail, search_result, source))

            conn.commit()

            # Copy uploaded image file
            if filename:
                id = dbCommon.path_to_id( cur, path, facility )
                target = '../database/' + enterprise + '/' + facility + '/images/' + id + '.jpg'
                shutil.copy2( filename, target );

            # Log activity
            facility_id = facility_name_to_id( facility )
            cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
                VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['add' + object_type], target_table, 'tail', tail, "Add " + object_type.lower() + ' ' + path, facility_id ) )

            conn.commit()


class updateCircuitObject:
    def __init__( self, by, id, object_type, parent_id, tail, voltage_id, room_id, description, filename, enterprise, facility ):
        open_database( enterprise )

        self.messages = []
        target_table = facility + '_CircuitObject'

        # Determine whether path is available
        if parent_id:
            ( test_id, path, source ) = test_path_availability( target_table, parent_id, tail )
        else:
            test_id = None
            path = tail
            source = ''

        if ( test_id != None ) and ( test_id != id ):
            # Path is neither available nor original
            self.messages.append( "Path '" + path + "' is not available." )

        else:
            # Path is either available or original; okay to update

            # Copy uploaded image file
            if filename:
                target = '../database/' + enterprise + '/' + facility + '/images/' + id + '.jpg'
                shutil.copy2( filename, target );

            # Get original path of target element
            cur.execute( 'SELECT path FROM ' + target_table + ' WHERE id = ?', (id,) )
            original_path = cur.fetchone()[0]

            # If path of target object is to change, update paths of all descendants
            if path != original_path:

                # Retrieve all descendants of the target object
                cur.execute( 'SELECT id, path FROM ' + target_table + ' WHERE path LIKE "' + original_path + '.%"' )
                descendants = cur.fetchall()

                # Update paths of all descendants
                for descendant in descendants:
                    descendant_id = descendant[0]
                    descendant_path = descendant[1]
                    new_descendant_path = descendant_path.replace( original_path, path, 1 )

                    # Update descendant row in database
                    cur.execute( 'UPDATE ' + target_table + ' SET path=? WHERE id=? ' , ( new_descendant_path, descendant_id ) )

            # Get fragments of search result text
            cur.execute('SELECT description FROM Voltage WHERE id = ?',(voltage_id,))
            voltage = cur.fetchone()[0]

            cur.execute('''SELECT room_num, old_num, description FROM ''' + facility + '''_Room WHERE id = ?''', (room_id,))
            loc = cur.fetchone()
            location = loc[0]
            location_old = loc[1]
            location_descr = loc[2]

            # Generate search result text
            search_result = dbCommon.make_search_result( source, voltage, location, location_old, location_descr, object_type, description, tail )

            # Update target object
            cur.execute( '''UPDATE ''' + target_table + ''' SET room_id=?, path=?, zone=?, voltage_id=?, description=?, parent_id=?, tail=?, search_result=?, source=? WHERE id=?''',
                ( room_id, path, '', voltage_id, description, parent_id, tail, search_result, source, id ) )

            # Log activity
            facility_id = facility_name_to_id( facility )
            cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
                VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['update' + object_type], target_table, 'tail', tail, "Update " + object_type.lower() + ' ' + path, facility_id ) )

            conn.commit()


class addDevice:
    def __init__( self, by, parent_id, name, room_id, description, enterprise, facility ):
        open_database( enterprise )

        # Add new object
        target_table = facility + '_Device'
        cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (room_id, parent_id, description, name)
             VALUES (?,?,?,?)''', (room_id, parent_id, description, name))

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['addDevice'], target_table, 'name', name, "Add device [" + description + "]", facility_id ) )

        conn.commit()

        self.success = True


class updateDevice:
    def __init__( self, by, id, parent_id, name, room_id, description, enterprise, facility ):
        open_database( enterprise )

        # Update specified object
        target_table = facility + '_Device'
        cur.execute( '''UPDATE ''' + target_table + ''' SET parent_id=?, name=?, room_id=?, description=? WHERE id=?''',
            ( parent_id, name, room_id, description, id ) )

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['updateDevice'], target_table, 'name', name, "Update device [" + description + "]", facility_id ) )

        conn.commit()

        self.success = True


class addLocation:
    def __init__( self, by, location, old_location, description, enterprise, facility ):
        open_database( enterprise )

        # Add new location
        target_table = facility + '_Room'
        cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (room_num, old_num, location_type, description)
            VALUES (?,?,?,?)''', (location, old_location, '', description) )

        # Log activity
        if location != '':
            target_column = 'room_num'
        else:
            target_column = 'old_num'

        formatted_location = dbCommon.format_location( location, old_location, description )

        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['addLocation'], target_table, target_column, formatted_location, 'Add location [' + formatted_location + ']', facility_id ) )

        conn.commit()

        self.success = True


class updateLocation:
    def __init__( self, by, id, location, old_location, description, enterprise, facility ):
        open_database( enterprise )

        # Update specified location
        target_table = facility + '_Room'

        cur.execute( '''UPDATE ''' + target_table + ''' SET room_num=?, old_num=?, description=? WHERE id=?''',
            ( location, old_location, description, id ) )


        # Update search results of circuit objects that refer to this location

        # Get circuit objects that refer to this location
        cur.execute('SELECT * FROM ' + facility + '_CircuitObject WHERE room_id = ?', (id,))
        rows = cur.fetchall()

        # Traverse circuit objects
        for row in rows:
            # Get search result fragments
            source = row[10]

            voltage_id = row[4]
            cur.execute('SELECT description FROM Voltage WHERE id = ?',(voltage_id,))
            voltage = cur.fetchone()[0]

            object_type = row[5]
            object_descr = row[6]
            tail = row[8]

            # Generate the search result
            search_result = dbCommon.make_search_result( source, voltage, location, old_location, description, object_type, object_descr, tail )

            # Save the new search result
            ptc_id = row[0]
            cur.execute( 'UPDATE ' + facility + '_CircuitObject SET search_result=? WHERE id=?', ( search_result, ptc_id ) )


        # Update descriptions of devices that refer to this location

        # Get devices that refer to this location
        cur.execute('SELECT * FROM ' + facility + '_Device WHERE room_id = ?', (id,))
        rows = cur.fetchall()

        # Traverse devices
        for row in rows:

            name = row[5]

            # Generate device description
            desc = dbCommon.append_location( '', location, old_location, description, '' )
            if desc:
                desc = name + ':' + desc
            else:
                desc = name

            dev_id = row[0]
            cur.execute( 'UPDATE ' + facility + '_Device SET description=? WHERE id=?', ( desc, dev_id ) )


        # Log activity
        if location != '':
            target_column = 'room_num'
        else:
            target_column = 'old_num'

        formatted_location = dbCommon.format_location( location, old_location, description )

        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['updateLocation'], target_table, target_column, formatted_location, 'Update location [' + formatted_location + ']', facility_id ) )

        conn.commit()

        self.success = True


class removeDevice:
    def __init__( self, by, id, comment, enterprise, facility ):
        open_database( enterprise )

        # Get row to be deleted
        target_table = facility + '_Device'
        cur.execute('SELECT * FROM ' + target_table + ' WHERE id = ?', (id,))
        row = cur.fetchone()
        room_id = row[1]
        parent_id = row[2]
        description = row[3]
        name = row[5]

        # Format origin text
        cur.execute( 'SELECT path FROM ' + facility + '_CircuitObject WHERE id = ?', (parent_id,))
        circuit = cur.fetchone()[0]

        if room_id != '':
            cur.execute('SELECT * FROM ' + facility + '_Room WHERE id = ?', (room_id,))
            room_row = cur.fetchone()
            loc_new = room_row[1]
            loc_old = room_row[2]
            loc_descr = room_row[4]
            formatted_location = dbCommon.format_location( loc_new, loc_old, loc_descr )
            where = ' <span class="glyphicon glyphicon-map-marker"></span>' + formatted_location
        else:
            where = ''

        origin = name + ' <span class="glyphicon glyphicon-arrow-up"></span>' + circuit + where

        # Create entry in Recycle Bin
        timestamp = time.time()
        recycle_table = facility + '_Recycle'
        cur.execute( 'INSERT INTO ' + recycle_table + ' ( remove_timestamp, remove_object_type, remove_object_origin, remove_comment, remove_object_id ) VALUES(?,?,?,?,?) ''', ( timestamp, 'Device', origin, comment, id ) )
        remove_id = cur.lastrowid

        # Insert target object in table of removed objects
        removed_table = facility + '_Removed_Device'
        cur.execute( 'INSERT INTO ' + removed_table + ' ( id, room_id, parent_id, description, power, name, remove_id ) VALUES(?,?,?,?,?,?,?) ''', ( row[0], row[1], row[2], row[3], row[4], row[5], remove_id ) )

        # Delete target object
        cur.execute( 'DELETE FROM ' + target_table + ' WHERE id=?', ( id, ) )

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['removeDevice'], target_table, 'name', name, "Remove device [" + description + "]", facility_id ) )

        conn.commit()

        self.success = True


class removeLocation:
    def __init__( self, by, id, comment, enterprise, facility ):
        open_database( enterprise )

        # Get row to be deleted
        target_table = facility + '_Room'
        cur.execute('SELECT * FROM ' + target_table + ' WHERE id = ?', (id,))
        row = cur.fetchone()

        # Format location
        loc_new = row[1]
        loc_old = row[2]
        loc_descr = row[4]
        formatted_location = dbCommon.format_location( loc_new, loc_old, loc_descr )

        # Create entry in Recycle Bin
        timestamp = time.time()
        recycle_table = facility + '_Recycle'
        cur.execute( 'INSERT INTO ' + recycle_table + ' ( remove_timestamp, remove_object_type, remove_object_origin, remove_comment, remove_object_id ) VALUES(?,?,?,?,?) ''', ( timestamp, 'Location', formatted_location, comment, id ) )
        remove_id = cur.lastrowid

        # Insert target object in table of removed objects
        removed_table = facility + '_Removed_Room'
        cur.execute( 'INSERT INTO ' + removed_table + ' ( id, room_num, old_num, location_type, description, remove_id ) VALUES(?,?,?,?,?,?) ''', ( row[0], row[1], row[2], row[3], row[4], remove_id ) )

        # Delete target object
        cur.execute( 'DELETE FROM ' + target_table + ' WHERE id=?', ( id, ) )

        # Log activity
        if loc_new != '':
            target_column = 'room_num'
        else:
            target_column = 'old_num'

        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( timestamp, by, dbCommon.dcEventTypes['removeLocation'], target_table, target_column, formatted_location, 'Remove location [' + formatted_location + ']', facility_id ) )

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
    def __init__(self, by, id, enterprise):
        open_database( enterprise )

        # Get username for reporting
        cur.execute( 'SELECT username FROM User WHERE id=?', ( id, ) )
        username = cur.fetchone()[0]
        self.username = username

        # Delete the user
        cur.execute( 'DELETE FROM User WHERE id=?', ( id, ) )

        # Report
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
        self.sources = get_circuit_object_dropdown( facility, '"Circuit"' )

        # Get all locations
        self.locations = get_location_dropdown( facility )


class circuitObjectDropdowns:
    def __init__(self, object_type, enterprise, facility):

        open_database( enterprise )

        # Get all potential parents
        sTypes = '"Panel"'
        if object_type == 'panel':
            sTypes += ',"Transformer"'
        self.parents = get_circuit_object_dropdown( facility, sTypes )

        # Get all locations
        self.locations = get_location_dropdown( facility )

        # Get all voltages
        cur.execute('SELECT id, description FROM Voltage')
        rows = cur.fetchall()

        voltages = []
        for row in rows:
            voltages.append( { 'id': row[0], 'text': row[1]  } )

        self.voltages = natsort.natsorted( voltages, key=lambda x: x['text'] )


class imageFilename:
    def __init__(self, path, enterprise, facility):

        open_database( enterprise )
        id = dbCommon.path_to_id( cur, path, facility )
        self.image_filename = id + '.jpg'


class restoreObject:
    def __init__(self, by, id, enterprise, facility):

        open_database( enterprise )

        # Get values representing object to be restored
        recycle_table = facility + '_Recycle'
        cur.execute( 'SELECT * FROM ' + recycle_table + ' WHERE id=?', ( id, ) );
        recycle_row = cur.fetchone()
        restore_object_type = recycle_row[2]

        # Handle according to object type
        if ( restore_object_type == 'Panel' ) or ( restore_object_type == 'Transformer' ) or ( restore_object_type == 'Circuit' ):

            source_table = facility + '_Removed_CircuitObject'
            target_table = facility + '_CircuitObject'
            # ????? Then what ?????

        elif restore_object_type == 'Device':
            self.restore_device( by, id, facility )
        elif restore_object_type == 'Location':
            self.restore_location( by, id, facility )

        # Clean up recyle bin
        cur.execute( 'DELETE FROM ' + recycle_table + ' WHERE id=?', ( id, ) );

        conn.commit()

        self.success = True


    def restore_device( self, by, id, facility ):

        # Determine source and target tables
        source_table = facility + '_Removed_Device'
        target_table = facility + '_Device'

        # ????? Then what ?????


    def restore_location( self, by, id, facility ):

        # Determine source and target tables
        source_table = facility + '_Removed_Room'
        target_table = facility + '_Room'

        # Get fields from source table
        cur.execute( 'SELECT * FROM ' + source_table + ' WHERE remove_id=?', ( id, ) );
        source_row = cur.fetchone()

        # Load fields into tuple
        restore_row = []
        for element in source_row:
            restore_row.append( element )
        restore_row.pop()

        # Restore object into target table
        cur.execute( 'INSERT INTO ' + target_table + ' (id, room_num, old_num, location_type, description) VALUES (?,?,?,?,?) ', tuple( restore_row ) )

        # Clean up removed object
        cur.execute( 'DELETE FROM ' + source_table + ' WHERE remove_id=?', ( id, ) );

        # Log activity
        loc_new = restore_row[1]
        loc_old = restore_row[2]
        loc_descr = restore_row[4]

        if loc_new != '':
            target_column = 'room_num'
        else:
            target_column = 'old_num'

        formatted_location = dbCommon.format_location( loc_new, loc_old, loc_descr )

        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description, facility_id )
            VALUES (?,?,?,?,?,?,?,? )''', ( time.time(), by, dbCommon.dcEventTypes['restoreLocation'], target_table, target_column, formatted_location, 'Restore location [' + formatted_location + ']', facility_id ) )
