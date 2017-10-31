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

DISTRIBUTION_OBJECT_FIELDS = '''
            id,
            path,
            object_type_id,
            parent_id,
            phase_b_parent_id,
            phase_c_parent_id,
            voltage_id,
            room_id,
            description,
            tail,
            search_result,
            source '''


def open_database( enterprise ):
    if enterprise != None:
        global conn
        global cur
        if conn == None:
            conn = sqlite3.connect('../database/' + enterprise + '/database.sqlite')
        if cur == None:
            cur = conn.cursor()


def make_device_label( name=None, parent_path=None, room_id=None, loc_new='', loc_old='', loc_descr='', facility=None ):

    label = ''

    # Concatenate path
    if parent_path:
        label += ' <span class="glyphicon glyphicon-arrow-up"></span>' + parent_path

    # Get location details
    if room_id:
        ( loc_new, loc_old, loc_descr ) = get_location( room_id, facility )

    # Concatenate location
    if loc_new or loc_old or loc_descr:
        label += ' <span class="glyphicon glyphicon-map-marker"></span>'
        label += dbCommon.format_location( loc_new, loc_old, loc_descr )

    # Prepend name
    label = label.strip()
    if label:
        label = name + ': ' + label
    else:
        label = name

    return label


def make_distribution_object_label( o ):
    label = ''

    print( o['object_type'] )

    if o['object_type'] in ( 'Panel', 'Transformer', 'Distribution' ):

        # Concatenate label fragments
        if o['source']:
            label += ' <span class="glyphicon glyphicon-arrow-up"></span>' + o['source']

        if o['voltage']:
            label += ' <span class="glyphicon glyphicon-flash"></span>' + o['voltage']

        if o['loc_new'] or o['loc_old'] or o['loc_descr']:
            label += ' <span class="glyphicon glyphicon-map-marker"></span>'
            label += dbCommon.format_location( o['loc_new'], o['loc_old'], o['loc_descr']);

    else:
        # Circuit - show description
        label = o['description']

    # Prepend name
    name = o['path'].split( '.' )[-1]
    label = label.strip()

    if label:
        label = name + ': ' + label
    else:
        label = name

    return label


def make_device_description( name, room_id, facility ):
    ( loc_new, loc_old, loc_descr ) = get_location( room_id, facility )
    description = dbCommon.format_device_description( name, loc_new, loc_old, loc_descr )
    return description


def facility_name_to_id( facility_name ):
    cur.execute( 'SELECT id FROM Facility WHERE facility_name=?', ( facility_name,) )
    facility_id = cur.fetchone()[0]
    return str( facility_id )


def username_to_id( username ):
    cur.execute( 'SELECT id FROM User WHERE lower( username )=?', ( username.lower(), ) )
    id = cur.fetchone()[0]
    return id


def username_to_role( username ):
    cur.execute( 'SELECT role_id FROM User WHERE lower( username )=?', ( username.lower(), ) )
    role_id = cur.fetchone()[0]
    cur.execute( 'SELECT role FROM Role WHERE id=?', ( role_id, ) )
    role = cur.fetchone()[0]
    return role


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


def get_distribution_dropdown( facility, aTypes ):

    dist_table = facility + '_Distribution'
    select_from_distribution( table=dist_table, fields=( dist_table + '.id, path, voltage_id' ), condition=( 'DistributionObjectType.object_type IN (' + ','.join( aTypes ) + ')' ) )
    rows = cur.fetchall()

    testId = 0
    objects = []
    for row in rows:
        testId = max( testId, row[0] )
        objects.append( { 'id': row[0], 'text': row[1], 'voltage_id': row[2], 'object_type': row[4] } )

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


def tail_to_number_name( tail ):

    aTail = tail.split( '-', maxsplit=1 )

    number = ''
    name = ''

    if aTail[0].isdigit():
        # Segment format is <number> or <number>-<name>.  Save the number.
        number = aTail[0]
        if len( aTail ) == 2:
            # Segment format is <number>-<name>.  Save the name.
            name = aTail[1]
    else:
        # Segment format is <notNumber>.  Save as name.
        name = tail

    return ( number, name )

def get_facility( facility_id ):

    facility_name = ''
    facility_fullname = ''

    if facility_id:
        cur.execute('SELECT facility_name, facility_fullname FROM Facility WHERE id = ?', (facility_id,))
        row = cur.fetchone()
        if row:
            facility_name = row[0]
            facility_fullname = row[1]

    return ( facility_name, facility_fullname )


def get_voltage( voltage_id ):
    cur.execute( 'SELECT voltage FROM Voltage WHERE id = ?', (voltage_id,) )
    voltage = cur.fetchone()[0]
    return voltage


def get_path( id, facility ):

    cur.execute('SELECT path FROM ' + facility + '_Distribution WHERE id = ?', (id,))
    path_row = cur.fetchone()

    if path_row:
        path = path_row[0]
    else:
        path = ''
    return path


def get_location( room_id, facility ):

    if str( room_id ).isdigit():
        cur.execute( 'SELECT room_num, old_num, description FROM ' + facility + '_Room WHERE id = ?', (room_id,) )
        loc = cur.fetchone()
        loc_new = loc[0]
        loc_old = loc[1]
        loc_descr = loc[2]
    else:
        loc_new = ''
        loc_old = ''
        loc_descr = ''

    return ( loc_new, loc_old, loc_descr )


def select_from_distribution( table=None, fields=None, condition='', params=None ):

    if not fields:
        fields = table + '.*'

    if condition:
        where = ' WHERE '
    else:
        where = ''

    sql = '''
      SELECT
        ''' + fields + ''',
        Voltage.voltage,
        DistributionObjectType.object_type
      FROM ''' + table + '''
        LEFT JOIN Voltage ON ''' + table + '''.voltage_id=Voltage.id
        LEFT JOIN DistributionObjectType ON ''' + table + '''.object_type_id=DistributionObjectType.id
      ''' + where + condition

    if params:
        cur.execute( sql, params )
    else:
        cur.execute( sql )


def summarize_distribution_object( id, facility ):

    dist_table = facility + '_Distribution'
    select_from_distribution( table=dist_table, fields='path, room_id, description', condition=(dist_table + '.id=?'), params=(id,) )

    row = cur.fetchone()
    path = row[0]
    room_id = row[1]
    description = row[2]
    voltage = row[3]

    loc = dbCommon.format_location( *get_location( room_id, facility ) )
    if loc:
        loc = ' [' + loc + ']'

    if description:
        description = ' "' + description + '"'

    summary = path + ' ' + voltage + loc + description

    return summary


def summarize_device( id, facility ):

    cur.execute('SELECT room_id, parent_id, name FROM ' + facility + '_Device WHERE id = ?', (id,))
    row = cur.fetchone()
    room_id = row[0]
    parent_id = row[1]
    name = row[2]

    circuit = get_path( parent_id, facility )

    loc = dbCommon.format_location( *get_location( room_id, facility ) )
    if loc:
        loc = ' [' + loc + ']'

    summary = "'" + name + "'" + ' on ' + circuit + loc
    return summary


def summarize_object( type, id, facility='' ):

    id = str( id )

    if type == 'Panel' or type == 'Transformer' or type == 'Circuit' :
        summary = summarize_distribution_object( id, facility )
    elif type == 'Device':
        summary = summarize_device( id, facility )
    elif type == 'Location':
        summary = dbCommon.format_location( *get_location( id, facility ) )
    elif type == 'User':
        summary = dbCommon.summarize_user( cur, id )

    return summary


def get_nearest_panel( type, id, facility ):

    if type == 'Panel':
        panel_id = id
        panel_path = get_path( id, facility )
    else:
        dist_table = facility + '_Distribution'
        device_table = facility + '_Device'

        if ( type == 'Device' ):
            initial_table = device_table
        else:
            initial_table = dist_table

        # Get parent_id of initial object
        cur.execute( 'SELECT parent_id from ' + initial_table + ' WHERE id=?', ( id, ) )
        parent_id = cur.fetchone()[0]

        # Traverse source hierarchy to nearest ancestor panel
        panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
        object_type_id = ''
        panel_id = ''
        panel_path = ''

        while object_type_id != panel_type_id:
            cur.execute( 'SELECT object_type_id, parent_id, id, path from ' + dist_table + ' WHERE id=?', ( parent_id, ) );
            row = cur.fetchone()
            object_type_id = row[0]
            parent_id = row[1]
            panel_id = row[2]
            panel_path = row[3]

    return ( str( panel_id ), panel_path )


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
                        ''' + facility + '''_Distribution.path,
                        ''' + facility + '''_Distribution.id
                    FROM ''' + facility + '''_Device
                        LEFT JOIN ''' + facility + '''_Distribution ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_Distribution.id)
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
        self.label = make_device_label( name=self.name, room_id=self.room_id, facility=facility )

        #gets room where device is located
        ( self.loc_new, self.loc_old, self.loc_descr ) = get_location( self.room_id, facility )
        formatted_location = dbCommon.format_location( self.loc_new, self.loc_old, self.loc_descr )

        # Get panel image
        ( panel_id, panel_path ) = get_nearest_panel( self.object_type, self.id, facility )
        filename = '../database/' + enterprise + '/' + facility + '/images/' + panel_id + '.jpg'
        if os.path.isfile( filename ):
            self.panel_image = panel_path
        else:
            self.panel_image = ''

        if user_role == 'Technician':
            facility_id = facility_name_to_id( facility )
            cur.execute( "SELECT timestamp, username, event_type, event_result FROM Activity WHERE facility_id = ? AND target_object_type = ? AND target_object_id = ?", (facility_id, self.object_type, self.id,) )
            self.events = cur.fetchall()
            self.events = natsort.natsorted( self.events, key=lambda x: x[0], reverse=True )
            self.update_device = self.id
            self.remove_device = self.id
            self.remove_what = 'name'
            self.formatted_location = formatted_location
            self.activity_log = self.id
        else:
            self.events = []


class distributionObject:

    def __init__(self,id=None,getkids=True,user_role=None,enterprise=None,facility=None):
        open_database( enterprise )

        dist_table = facility + '_Distribution'

        if id:
            select_from_distribution( table=dist_table, condition=(dist_table + '.id = ?'), params=(id,) )
        else:
            select_from_distribution( table=dist_table, condition='parent_id=""' )

        #initialize distribution object properties
        row = cur.fetchone()
        self.id = str( row[0] )
        self.path = row[1]
        self.object_type_id = row[2]
        self.parent_id = row[3]
        self.phase_b_parent_id = row[4]
        self.phase_c_parent_id = row[5]
        # self.voltage_id = row[6]
        self.room_id = row[7]
        self.description = row[8]
        # self.tail = row[9]
        # self.search_result = row[10]
        self.source = row[11]
        self.voltage = row[12]
        self.object_type = row[13]

        if self.object_type == 'Circuit':
            self.circuit_descr = self.description
        elif self.object_type == 'Panel':
            self.panel_descr = self.description
        elif self.object_type == 'Transformer':
            self.transformer_descr = self.description

        # Retrieve parent path
        self.parent_path = get_path( self.parent_id, facility )

        # Get room information
        ( self.loc_new, self.loc_old, self.loc_descr ) = get_location( self.room_id, facility )

        # Generate label
        self.label = make_distribution_object_label( self.__dict__ )

        # Add image filename
        filename = '../database/' + enterprise + '/' + facility + '/images/' + self.id + '.jpg'
        if os.path.isfile( filename ):
            self.panel_image = filename
        else:
            self.panel_image = ''


        if getkids:

            # Retrieve children
            cur.execute('SELECT id, path FROM ' + facility + '_Distribution WHERE parent_id = ?', (self.id,))
            child_paths = cur.fetchall()
            self.children = []

            for i in range( len( child_paths ) ):
                child_id = str( child_paths[i][0] )
                child_path = child_paths[i][1]
                child = distributionObject( id=child_id, getkids=False, enterprise=enterprise, facility=facility )
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
                            ''' + facility + '''_Distribution.id,
                            ''' + facility + '''_Distribution.path
                        FROM ''' + facility + '''_Device
                            LEFT JOIN ''' + facility + '''_Distribution ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_Distribution.id)
                        WHERE path = ?''', (self.path,) )

            dev_ids = cur.fetchall()
            self.devices = []
            for i in range( len (dev_ids) ):
                dev_id = dev_ids[i][0]
                dev = device( id=dev_id, enterprise=enterprise, facility=facility )
                self.devices.append( [ dev.id, dev.loc_new, dev.loc_old, dev.loc_descr, dev.description, dev.label ] )


        if user_role == 'Technician':
            facility_id = facility_name_to_id( facility )
            cur.execute( "SELECT timestamp, username, event_type, event_result FROM Activity WHERE facility_id = ? AND target_object_type = ? AND target_object_id = ?", (facility_id, self.object_type, self.id,) )
            self.events = cur.fetchall()
            self.events = natsort.natsorted( self.events, key=lambda x: x[0], reverse=True )
        else:
            self.events = []


class search:
    def __init__(self, searchText, searchTargets=None, enterprise=None, facility=None):
        open_database( enterprise )

        if searchTargets:
            aTargets = searchTargets.split( ',' )
        else:
            aTargets = ['All']

        # Search Distribution paths
        if ( 'All' in aTargets ) or ( 'Path' in aTargets ):
            cur.execute('SELECT path, path FROM ' + facility + '_Distribution WHERE tail LIKE "%' + searchText + '%"')
            pathRows = cur.fetchall()
        else:
            pathRows = []


        # Search Distribution objects
        if ( 'All' in aTargets ) or ( 'Circuit' in aTargets ) or ( 'Panel' in aTargets ) or ( 'Transformer' in aTargets ):

            # Generate condition to select requested object types
            if ( 'All' in aTargets ) or ( ( 'Circuit' in aTargets ) and ( 'Panel' in aTargets ) and ( 'Transformer' in aTargets ) ):
                sWhere = ''
            else:
                aWhere = []
                if ( 'Panel' in aTargets ):
                    aWhere.append( "'Panel'" )
                if ( 'Transformer' in aTargets ):
                    aWhere.append( "'Transformer'" )
                if ( 'Circuit' in aTargets ):
                    aWhere.append( "'Circuit'" )

                sWhere = ' WHERE DistributionObjectType.object_type IN (' + ','.join( aWhere ) + ')'

            cur.execute(
              '''SELECT path, search_result
                  FROM
                    (SELECT
                        ''' + facility + '''_Distribution.path,
                        ''' + facility + '''_Distribution.search_result,
                        DistributionObjectType.object_type,
                        ''' + facility + '''_Distribution.tail AS tail,
                        ''' + facility + '''_Distribution.source AS source,
                        ''' + facility + '''_Distribution.description AS description,
                        Voltage.voltage AS voltage,
                        ''' + facility + '''_Room.room_num AS location,
                        ''' + facility + '''_Room.old_num AS location_old,
                        ''' + facility + '''_Room.description AS location_descr
                    FROM ''' + facility + '''_Distribution
                        LEFT JOIN DistributionObjectType ON ''' + facility + '''_Distribution.object_type_id = DistributionObjectType.id
                        LEFT JOIN Voltage ON ''' + facility + '''_Distribution.voltage_id = Voltage.id
                        LEFT JOIN ''' + facility + '''_Room ON ''' + facility + '''_Distribution.room_id = ''' + facility + '''_Room.id
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

            distributionRows = cur.fetchall()
        else:
            distributionRows = []


        # Search devices
        if ( 'All' in aTargets ) or ( 'Device' in aTargets ):
            cur.execute(
              '''SELECT path, description
                  FROM
                  (SELECT
                    ''' + facility + '''_Distribution.path || "." || ''' + facility + '''_Device.id AS path,
                    ''' + facility + '''_Device.description,
                    ''' + facility + '''_Distribution.id,
                    ''' + facility + '''_Device.name AS name,
                    ''' + facility + '''_Room.room_num AS location,
                    ''' + facility + '''_Room.old_num AS location_old,
                    ''' + facility + '''_Room.description AS location_descr
                  FROM ''' + facility + '''_Device
                    LEFT JOIN ''' + facility + '''_Distribution ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_Distribution.id
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
        self.searchResults = pathRows + distributionRows + devRows


class sortableTable:
    def __init__(self, table_object_type=None, user_role=None, target_object_type=None, target_object_id=None, enterprise=None, facility=None):
        open_database( enterprise )

        self.rows = []

        if table_object_type == 'Recycle':
            recycle_table = facility + '_Recycle'
            cur.execute( 'SELECT * FROM ' + recycle_table )
            objects = cur.fetchall()

            for obj in objects:

                recycle_id = obj[0]
                timestamp = obj[1]
                remove_object_type = obj[2]
                parent_path = obj[3]
                loc_new = obj[4]
                loc_old = obj[5]
                loc_descr = obj[6]
                remove_comment = obj[7]
                remove_object_id = obj[8]

                if ( remove_object_type == 'Panel' ) or ( remove_object_type == 'Transformer' ) or ( remove_object_type == 'Circuit' ) :
                    dist_table = facility + '_Removed_Distribution'
                    select_from_distribution( table=dist_table, condition=(dist_table + '.id=?'), params=(remove_object_id,) )
                    ptc_row = cur.fetchone()
                    room_id = ptc_row[7]
                    voltage_id = ptc_row[6]
                    description = ptc_row[8]
                    parent_id = ptc_row[3]
                    tail = ptc_row[9]
                    voltage = ptc_row[13]
                    path = parent_path + '.' + tail
                    ( number, name ) = tail_to_number_name( tail )

                    fields = { 'parent_id': parent_id, 'number': number, 'name': name, 'room_id': room_id, 'voltage_id': voltage_id }

                    ptc = { 'object_type': 'Distribution', 'source': parent_path, 'voltage': voltage, 'loc_new': loc_new, 'loc_old': loc_old, 'loc_descr': loc_descr, 'description': description, 'path': path }
                    origin = make_distribution_object_label( ptc )

                if remove_object_type == 'Device':
                    cur.execute('SELECT * FROM ' + facility + '_Removed_Device WHERE id = ?', (remove_object_id,))
                    device_row = cur.fetchone()
                    room_id = device_row[1]
                    parent_id = device_row[2]
                    name = device_row[5]
                    origin = make_device_label( name=name, parent_path=parent_path, loc_new=loc_new, loc_old=loc_old, loc_descr=loc_descr, facility=facility )

                    fields = { 'name': name, 'parent_id': parent_id, 'room_id': room_id }

                elif remove_object_type == 'Location':
                    cur.execute('SELECT * FROM ' + facility + '_Removed_Room WHERE id = ?', (remove_object_id,))
                    room = cur.fetchone()
                    fields = { 'loc_new': room[1], 'loc_old': room[2], 'loc_descr': room[4] }
                    origin = '<span class="glyphicon glyphicon-map-marker"></span>' + dbCommon.format_location( loc_new, loc_old, loc_descr )

                row = { 'id': recycle_id, 'timestamp': timestamp, 'remove_object_type': remove_object_type, 'remove_object_origin': origin, 'remove_comment': remove_comment, 'remove_object_id': remove_object_id, 'restore_object': recycle_id, 'fields': fields }
                self.rows.append( row )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['timestamp'], reverse=True )

        elif table_object_type == 'Activity':
            # Retrieve all objects of requested type
            if target_object_type and target_object_id:
                where = ' WHERE target_object_type="' + target_object_type + '" AND target_object_id="' + target_object_id + '"'
            else:
                where = ''
            cur.execute('SELECT * FROM Activity' + where)
            objects = cur.fetchall()

            # Make table rows
            for obj in objects:
                if target_object_type and target_object_id:
                    facility_fullname = ''
                    event_target = ''
                else:
                    ( facility, facility_fullname ) = get_facility( obj[4] )
                    event_target = obj[5]

                row = { 'id': obj[0], 'timestamp': obj[1], 'event_type': obj[2], 'event_trigger': obj[3], 'facility_fullname': facility_fullname, 'event_target': event_target, 'event_result': obj[6] }

                self.rows.append( row )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['timestamp'], reverse=True )

        elif table_object_type == 'User':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM User')
            objects = cur.fetchall()

            # Make table rows
            for obj in objects:
                role_id = obj[3]
                if role_id:
                    row = userTableRow( row=obj, enterprise=enterprise )
                    self.rows.append( row.__dict__ )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['username'] )

        elif table_object_type == 'Device':
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
                        ''' + facility + '''_Distribution.path,
                        ''' + facility + '''_Distribution.id
                    FROM ''' + facility + '''_Device
                        LEFT JOIN ''' + facility + '''_Distribution ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_Distribution.id)''')

            objects = cur.fetchall()

            # Add other fields to each row
            for obj in objects:
                row = device( row=obj, enterprise=enterprise, facility=facility, user_role=user_role )
                self.rows.append( row.__dict__ )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['source_path'] )

        elif table_object_type == 'Location':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM ' + facility + '_Room')
            objects = cur.fetchall()

            # Add other fields to each row
            for obj in objects:
                row = location( row=obj, facility=facility, user_role=user_role )
                self.rows.append( row.__dict__ )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['loc_new'] )

        else:
            # Retrieve all objects of requested type
            dist_table = facility + '_Distribution'
            select_from_distribution( table=dist_table, condition='object_type=?', params=(table_object_type,) )
            objects = cur.fetchall()

            # Add other fields to each row
            for obj in objects:
                row = distributionTableRow( row=obj, user_role=user_role, enterprise=enterprise, facility=facility )
                self.rows.append( row.__dict__ )

            self.rows = natsort.natsorted( self.rows, key=lambda x: x['path'] )

        print('found ' + str(len(self.rows)) + ' rows' )


class userTableRow:
    def __init__(self, user_id=None, row=None, enterprise=None):
        open_database( enterprise )

        if not row:
            cur.execute('SELECT * FROM User WHERE id = ?', (user_id,))
            row = cur.fetchone()

        role_id = row[3]
        cur.execute('SELECT role FROM Role WHERE id = ?', (role_id,))
        role = cur.fetchone()[0]

        id = row[0]
        username = row[1]

        if role == 'Administrator':
            remove_id = ''
            remove_what = ''
        else:
            remove_id = id
            remove_what = 'username'

        facilities = authFacilities( username, enterprise )
        auth_facilities = '<br/>'.join( facilities.sorted_fullnames )
        facilities_maps = facilities.__dict__

        if row[6]:
            sStatus = 'Enabled'
        else:
            sStatus = 'Disabled'

        self.id = id
        self.username = username
        self.role = role
        self.auth_facilities = auth_facilities
        self.facilities_maps = facilities_maps
        self.update_user = id
        self.remove_user = remove_id
        self.remove_what = remove_what
        self.status = sStatus
        self.first_name = row[7]
        self.last_name = row[8]
        self.email_address = row[9]
        self.organization = row[10]
        self.user_description = row[4]


class location:
    def __init__(self, id=None, row=None, enterprise=None, facility=None, user_role=None):
        open_database( enterprise )

        if not row:
            cur.execute('SELECT * FROM ' + facility + '_Room WHERE id = ?', (id,))
            row = cur.fetchone()

        self.id = row[0]
        self.loc_new = row[1]
        self.loc_old = row[2]
        self.loc_descr = row[4]

        cur.execute('SELECT COUNT(*) FROM ' + facility + '_Device WHERE room_id = ?', (self.id,))
        self.devices = cur.fetchone()[0]

        object_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
        cur.execute('SELECT COUNT(*) FROM ' + facility + '_Distribution WHERE room_id=? AND object_type_id=?', ( self.id, object_type_id ))
        self.panels = cur.fetchone()[0]

        object_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )
        cur.execute('SELECT COUNT(*) FROM ' + facility + '_Distribution WHERE room_id=? AND object_type_id=?', ( self.id, object_type_id ))
        self.transformers = cur.fetchone()[0]

        object_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )
        cur.execute('SELECT COUNT(*) FROM ' + facility + '_Distribution WHERE room_id=? AND object_type_id=?', ( self.id, object_type_id ))
        self.circuits = cur.fetchone()[0]

        if user_role == 'Technician':
            self.activity_log = self.id
            self.update_location = self.id
            self.formatted_location = dbCommon.format_location( self.loc_new, self.loc_old, self.loc_descr )
            self.remove_what = 'formatted_location'
            if ( self.devices + self.panels + self.transformers + self.circuits ) == 0:
                self.remove_location = self.id
            else:
                self.remove_location = ''


class distributionTableRow:

    def __init__( self, row=None, id=None, user_role=None, enterprise=None, facility=None ):

        open_database( enterprise )

        if not row:
            dist_table = facility + '_Distribution'
            select_from_distribution( table=dist_table, condition=(dist_table + '.id=?'), params=(id,) )
            row = cur.fetchone()

        self.id = str( row[0] )
        self.path = row[1]
        self.object_type_id = row[2]
        self.parent_id = row[3]
        self.phase_b_parent_id = row[4]
        self.phase_c_parent_id = row[5]
        self.voltage_id = row[6]
        self.room_id = row[7]
        self.description = row[8]
        # self.tail = row[9]
        # self.search_result = row[10]
        self.source = row[11]
        self.voltage = row[12]
        self.object_type = row[13]

        # Extract number and name from path tail
        tail = self.path.split('.')[-1]
        ( self.number, self.name ) = tail_to_number_name( tail )

        ( self.loc_new, self.loc_old, self.loc_descr ) = get_location( self.room_id, facility )
        self.formatted_location = dbCommon.format_location( self.loc_new, self.loc_old, self.loc_descr )

        # Add image filename
        ( panel_id, panel_path ) = get_nearest_panel( self.object_type, self.id, facility )
        filename = '../database/' + enterprise + '/' + facility + '/images/' + panel_id + '.jpg'
        if os.path.isfile( filename ):
            self.panel_image = panel_path
        else:
            self.panel_image = ''

        cur.execute('SELECT COUNT(id) FROM ' + facility + '_Distribution WHERE parent_id = ?', (self.id,))
        self.children = cur.fetchone()[0]

        cur.execute(
          '''SELECT COUNT( device_id )
                FROM
                    (SELECT
                        ''' + facility + '''_Device.id AS device_id,
                        ''' + facility + '''_Device.parent_id,
                        ''' + facility + '''_Distribution.id,
                        ''' + facility + '''_Distribution.path
                    FROM ''' + facility + '''_Device
                        LEFT JOIN ''' + facility + '''_Distribution ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_Distribution.id)
                    WHERE path = ?''', (self.path,) )

        self.devices = cur.fetchone()[0]

        if self.object_type == 'Circuit':
            self.circuit_descr = self.description
        elif self.object_type == 'Panel':
            self.panel_descr = self.description
        elif self.object_type == 'Transformer':
            self.transformer_descr = self.description

        if user_role == 'Technician':
            self.activity_log = self.id
            if self.object_type == 'Circuit':
                self.update_circuit = self.id
                self.remove_circuit = self.id
                self.remove_what = 'path'
            elif self.object_type == 'Panel':
                self.update_panel = self.id
                if self.parent_id:
                    self.remove_panel = self.id
                    self.remove_what = 'path'
                else:
                    # Prohibit removal of root panel
                    self.remove_panel = ''
                    self.remove_what = ''
            elif self.object_type == 'Transformer':
                self.update_transformer = self.id
                self.remove_transformer = self.id
                self.remove_what = 'path'


class addNote:
    def __init__(self, args):

        open_database( args.enterprise )

        # Add note to Activity log
        facility_id = facility_name_to_id( args.facility )
        object_type = args.object_type
        object_id = args.object_id
        note = args.note
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['addNote'], args.username, facility_id, summarize_object( object_type, object_id, args.facility ), note, object_type, object_id  ) )
        note_id = cur.lastrowid

        conn.commit()

        # Return row
        cur.execute( 'SELECT * FROM Activity WHERE id=?', ( note_id, ) )
        row = cur.fetchone()
        self.row = { 'id': row[0], 'timestamp': row[1], 'event_type': row[2], 'event_trigger': row[3], 'facility_fullname': '', 'event_target': '', 'event_result': row[6] }
        self.messages = []


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


class addDistributionObject:
    def __init__( self, by, object_type, parent_id, tail, voltage_id, room_id, description, filename, enterprise, facility ):
        open_database( enterprise )

        self.messages = []
        self.selectors = []
        self.row = {}
        target_table = facility + '_Distribution'

        # Determine whether path is available
        ( test_id, path, source ) = test_path_availability( target_table, parent_id, tail )

        if test_id:
            # Path already in use
            self.messages.append( "Path '" + path + "' is not available." )
            self.selectors = [ '#parent_path', '#number', '#name' ];

        else:
            # Path is not in use; okay to add

            # Generate search result text
            voltage = get_voltage( voltage_id )
            ( loc_new, loc_old, loc_descr ) = get_location( room_id, facility )
            search_result = dbCommon.make_search_result( source, voltage, loc_new, loc_old, loc_descr, object_type, description, tail )

            # Add new object
            object_type_id = dbCommon.object_type_to_id( cur, object_type )
            cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (room_id, path, voltage_id, object_type_id, description, parent_id, tail, search_result, source)
                 VALUES (?,?,?,?,?,?,?,?,?)''', (room_id, path, voltage_id, object_type_id, description, parent_id, tail, search_result, source))
            target_object_id = cur.lastrowid

            # Copy uploaded image file
            if filename:
                id = dbCommon.path_to_id( cur, path, facility )
                target = '../database/' + enterprise + '/' + facility + '/images/' + id + '.jpg'
                shutil.copy2( filename, target );

            # Log activity
            facility_id = facility_name_to_id( facility )
            cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
                VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['add' + object_type], by, facility_id, '', summarize_object( object_type, target_object_id, facility ), object_type, target_object_id  ) )

            conn.commit()

            # Return row
            row = distributionTableRow( id=target_object_id, user_role=username_to_role( by ), enterprise=enterprise, facility=facility )
            self.row = row.__dict__


class updateDistributionObject:
    def __init__( self, by, id, object_type, parent_id, tail, voltage_id, room_id, description, filename, enterprise, facility ):
        open_database( enterprise )

        # Get initial state of object for Activity log
        before_summary = summarize_object( object_type, id, facility )

        # Initialize return values
        self.messages = []
        self.selectors = []
        self.row = {}
        self.descendant_rows = []

        target_table = facility + '_Distribution'
        user_role = username_to_role( by )

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
            self.selectors = [ '#parent_path', '#number', '#name' ];

        else:
            # Path is either available or original; okay to update

            # Copy uploaded image file
            if filename:
                target = '../database/' + enterprise + '/' + facility + '/images/' + id + '.jpg'
                shutil.copy2( filename, target );

            # Get original path of target element
            original_path = get_path( id, facility )

            # If path of target object is to change, update paths of all descendants
            if path != original_path:

                # Retrieve all descendants of the target object
                select_from_distribution( table=target_table, condition=('path LIKE "' + original_path + '.%"') )
                descendants = cur.fetchall()

                # Update path, search result, and source of all descendants
                for desc in descendants:
                    desc_id = str( desc[0] )
                    desc_path = desc[1]
                    desc_room_id = desc[7]
                    desc_description = desc[8]
                    desc_tail = desc[9]
                    desc_voltage = desc[12]
                    desc_object_type = desc[13]

                    ( desc_loc_new, desc_loc_old, desc_loc_descr ) = get_location( desc_room_id, facility )
                    new_desc_path = desc_path.replace( original_path, path, 1 )
                    new_desc_source = new_desc_path.split( '.' )[-2]
                    desc_search_result = dbCommon.make_search_result( new_desc_source, desc_voltage, desc_loc_new, desc_loc_old, desc_loc_descr, desc_object_type, desc_description, desc_tail )
                    cur.execute( 'UPDATE ' + target_table + ' SET path=?, search_result=?, source=? WHERE id=? ' , ( new_desc_path, desc_search_result, new_desc_source, desc_id ) )

                    # If path is affected in descendant of same object type, return descendant row, so that GUI will update in table
                    if ( object_type == desc_object_type ) and ( desc_path != new_desc_path ):
                        desc_row = distributionTableRow( id=desc_id, user_role=user_role, enterprise=enterprise, facility=facility )
                        self.descendant_rows.append( desc_row.__dict__ )

            # Generate search result text
            voltage = get_voltage( voltage_id )
            ( loc_new, loc_old, loc_descr ) = get_location( room_id, facility )
            search_result = dbCommon.make_search_result( source, voltage, loc_new, loc_old, loc_descr, object_type, description, tail )

            # Update target object
            cur.execute( '''UPDATE ''' + target_table + ''' SET room_id=?, path=?, voltage_id=?, description=?, parent_id=?, tail=?, search_result=?, source=? WHERE id=?''',
                ( room_id, path, voltage_id, description, parent_id, tail, search_result, source, id ) )

            # Log activity
            facility_id = facility_name_to_id( facility )
            cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
                VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['update' + object_type], by, facility_id, before_summary, summarize_object( object_type, id, facility ), object_type, id  ) )

            conn.commit()

            # Return updated row
            row = distributionTableRow( id=id, user_role=user_role, enterprise=enterprise, facility=facility )
            self.row = row.__dict__


class addDevice:
    def __init__( self, by, parent_id, name, room_id, enterprise, facility ):
        open_database( enterprise )

        # Generate new description
        description = make_device_description( name, room_id, facility )

        # Add new object
        target_table = facility + '_Device'
        cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (room_id, parent_id, description, name)
             VALUES (?,?,?,?)''', (room_id, parent_id, description, name))
        target_object_id = cur.lastrowid

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['addDevice'], by, facility_id, '', summarize_object( 'Device', target_object_id, facility ), 'Device', target_object_id  ) )

        conn.commit()

        # Return row
        row = device( id=target_object_id, enterprise=enterprise, facility=facility, user_role=username_to_role( by ) )
        self.row = row.__dict__
        self.messages = []
        self.descendant_rows = []


class updateDevice:
    def __init__( self, by, id, parent_id, name, room_id, enterprise, facility ):
        open_database( enterprise )

        # Get initial state of object for Activity log
        before_summary = summarize_object( 'Device', id, facility )

        # Generate new description
        description = make_device_description( name, room_id, facility )

        # Update specified object
        target_table = facility + '_Device'
        cur.execute( '''UPDATE ''' + target_table + ''' SET parent_id=?, name=?, room_id=?, description=? WHERE id=?''',
            ( parent_id, name, room_id, description, id ) )

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['updateDevice'], by, facility_id, before_summary, summarize_object( 'Device', id, facility ), 'Device', id  ) )

        conn.commit()

        # Return row
        self.messages = []
        self.selectors = []
        self.descendant_rows = []
        row = device( id=id, enterprise=enterprise, facility=facility, user_role=username_to_role( by ) )
        self.row = row.__dict__


class addLocation:
    def __init__( self, by, loc_new, loc_old, loc_descr, enterprise, facility ):
        open_database( enterprise )

        # Add new location
        target_table = facility + '_Room'
        cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (room_num, old_num, location_type, description)
            VALUES (?,?,?,?)''', (loc_new, loc_old, '', loc_descr) )
        target_object_id = cur.lastrowid

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['addLocation'], by, facility_id, '', summarize_object( 'Location', target_object_id, facility ), 'Location', target_object_id  ) )

        conn.commit()

        # Return row
        row = location( id=target_object_id, enterprise=enterprise, facility=facility, user_role=username_to_role( by ) )
        self.row = row.__dict__
        self.messages = []


class updateLocation:
    def __init__( self, by, id, loc_new, loc_old, loc_descr, enterprise, facility ):
        open_database( enterprise )

        # Get initial state of object for Activity log
        before_summary = summarize_object( 'Location', id, facility )

        # Update specified location
        target_table = facility + '_Room'
        cur.execute( '''UPDATE ''' + target_table + ''' SET room_num=?, old_num=?, description=? WHERE id=?''',
            ( loc_new, loc_old, loc_descr, id ) )


        # Update search results of distribution objects that refer to this location

        # Get distribution objects that refer to this location
        dist_table = facility + '_Distribution'
        select_from_distribution( table=dist_table, condition=(dist_table + '.room_id=?'), params=(id,) )
        rows = cur.fetchall()

        # Traverse distribution objects
        for row in rows:
            # Get search result fragments
            object_descr = row[8]
            tail = row[9]
            source = row[11]
            voltage = row[12]
            object_type = row[13]

            # Generate the search result
            search_result = dbCommon.make_search_result( source, voltage, loc_new, loc_old, loc_descr, object_type, object_descr, tail )

            # Save the new search result
            ptc_id = row[0]
            cur.execute( 'UPDATE ' + facility + '_Distribution SET search_result=? WHERE id=?', ( search_result, ptc_id ) )


        # Update descriptions of devices that refer to this location

        # Get devices that refer to this location
        cur.execute('SELECT * FROM ' + facility + '_Device WHERE room_id = ?', (id,))
        rows = cur.fetchall()

        # Traverse devices
        for row in rows:

            name = row[5]

            # Generate device description
            desc = dbCommon.append_location( '', loc_new, loc_old, loc_descr, '' )
            if desc:
                desc = name + ':' + desc
            else:
                desc = name

            dev_id = row[0]
            cur.execute( 'UPDATE ' + facility + '_Device SET description=? WHERE id=?', ( desc, dev_id ) )


        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['updateLocation'], by, facility_id, before_summary, summarize_object( 'Location', id, facility ), 'Location', id  ) )

        conn.commit()

        # Return row
        self.messages = []
        self.selectors = []
        self.descendant_rows = []
        row = location( id=id, enterprise=enterprise, facility=facility, user_role=username_to_role( by ) )
        self.row = row.__dict__


class removeDistributionObject:
    def __init__( self, by, id, comment, enterprise, facility ):
        open_database( enterprise )

        # Get row to be deleted
        target_table = facility + '_Distribution'
        select_from_distribution( table=target_table, condition=(target_table + '.id=?'), params=(id,) )
        row = cur.fetchone()
        path = row[1]
        parent_id = row[3]
        room_id = row[7]
        object_type = row[13]

        # Get initial state of object for Activity log
        before_summary = summarize_object( object_type, id, facility )

        # Get parent path
        parent_path = get_path( parent_id, facility )

        # Get location
        ( loc_new, loc_old, loc_descr ) = get_location( room_id, facility )

        # Create entry in Recycle Bin
        timestamp = time.time()
        recycle_table = facility + '_Recycle'
        cur.execute( 'INSERT INTO ' + recycle_table + ' ( remove_timestamp, remove_object_type, parent_path, loc_new, loc_old, loc_descr, remove_comment, remove_object_id ) VALUES(?,?,?,?,?,?,?,?) ', ( timestamp, object_type, parent_path, loc_new, loc_old, loc_descr, comment, id ) )
        remove_id = cur.lastrowid

        # Insert target object in table of removed objects
        removed_table = facility + '_Removed_Distribution'
        row = list( row )
        row.pop()
        row.pop()
        row = tuple( row )
        cur.execute( 'INSERT INTO ' + removed_table + ' ( ' + DISTRIBUTION_OBJECT_FIELDS + ', remove_id ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?) ', ( *row, remove_id ) )

        # Delete target object
        cur.execute( 'DELETE FROM ' + target_table + ' WHERE id=?', ( id, ) )
        self.removed_object_ids = [id]

        # Retrieve all devices attached to removed object
        dev_table = facility + '_Device'
        cur.execute( 'SELECT * FROM ' + dev_table + ' WHERE parent_id=?', ( id,) )
        devices = cur.fetchall()

        # Move all directly attached devices to 'Removed' table
        removed_dev_table = facility + '_Removed_Device'
        for dev in devices:
            device_id = dev[0]
            cur.execute( 'INSERT INTO ' + removed_dev_table + ' ( id, room_id, parent_id, description, power, name, remove_id ) VALUES(?,?,?,?,?,?,?) ', ( *dev, remove_id ) )
            cur.execute( 'DELETE FROM ' + dev_table + ' WHERE id=?', ( device_id, ) )

        # Retrieve all descendants of deleted object
        select_from_distribution( table=target_table, condition=( 'path LIKE "' + path + '.%"' ) )
        descendants = cur.fetchall()

        # Move all descendants and their respective attached devices to 'Removed' tables
        for desc in descendants:
            descendant_id = desc[0]
            desc_object_type = desc[13]

            if object_type == desc_object_type:
                self.removed_object_ids.append( descendant_id )

            # Move current descendant to 'Removed' table
            removed_desc = list( desc )
            removed_desc.pop()
            removed_desc.pop()
            removed_desc = tuple( removed_desc )
            cur.execute( 'INSERT INTO ' + removed_table + ' ( ' + DISTRIBUTION_OBJECT_FIELDS + ', remove_id ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?) ', ( *removed_desc, remove_id ) )
            cur.execute( 'DELETE FROM ' + target_table + ' WHERE id=?', ( descendant_id, ) )

            # Retrieve all devices attached to current descendant
            cur.execute( 'SELECT * FROM ' + dev_table + ' WHERE parent_id=?', ( descendant_id,) )
            devices = cur.fetchall()

            # Move all devices attached to current descendant
            for dev in devices:
                device_id = dev[0]
                cur.execute( 'INSERT INTO ' + removed_dev_table + ' ( id, room_id, parent_id, description, power, name, remove_id ) VALUES(?,?,?,?,?,?,?) ', ( *dev, remove_id ) )
                cur.execute( 'DELETE FROM ' + dev_table + ' WHERE id=?', ( device_id, ) )

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['remove' + object_type], by, facility_id, before_summary, comment, object_type, id  ) )

        conn.commit()


class removeDevice:
    def __init__( self, by, id, comment, enterprise, facility ):
        open_database( enterprise )

        # Get initial state of object for Activity log
        before_summary = summarize_object( 'Device', id, facility )

        # Get row to be deleted
        target_table = facility + '_Device'
        cur.execute('SELECT * FROM ' + target_table + ' WHERE id = ?', (id,))
        row = cur.fetchone()
        room_id = row[1]
        parent_id = row[2]

        # Get parent path
        parent_path = get_path( parent_id, facility )

        # Get location
        ( loc_new, loc_old, loc_descr ) = get_location( room_id, facility )

        # Create entry in Recycle Bin
        timestamp = time.time()
        recycle_table = facility + '_Recycle'
        object_type = 'Device'
        cur.execute( 'INSERT INTO ' + recycle_table + ''' ( remove_timestamp, remove_object_type, parent_path, loc_new, loc_old, loc_descr, remove_comment, remove_object_id )
            VALUES(?,?,?,?,?,?,?,?) ''',( timestamp, object_type, parent_path, loc_new, loc_old, loc_descr, comment, id ) )
        remove_id = cur.lastrowid

        # Insert target object in table of removed objects
        removed_table = facility + '_Removed_Device'
        cur.execute( 'INSERT INTO ' + removed_table + ' ( id, room_id, parent_id, description, power, name, remove_id ) VALUES(?,?,?,?,?,?,?) ', ( *row, remove_id ) )

        # Delete target object
        cur.execute( 'DELETE FROM ' + target_table + ' WHERE id=?', ( id, ) )
        self.removed_object_ids = [id]

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['removeDevice'], by, facility_id, before_summary, comment, 'Device', id  ) )

        conn.commit()



class removeLocation:
    def __init__( self, by, id, comment, enterprise, facility ):
        open_database( enterprise )

        # Get initial state of object for Activity log
        before_summary = summarize_object( 'Location', id, facility )

        # Get row to be deleted
        target_table = facility + '_Room'
        cur.execute('SELECT * FROM ' + target_table + ' WHERE id = ?', (id,))
        row = cur.fetchone()
        loc_new = row[1]
        loc_old = row[2]
        loc_descr = row[4]

        # Create entry in Recycle Bin
        timestamp = time.time()
        recycle_table = facility + '_Recycle'
        object_type = 'Location'
        parent_path = ''
        cur.execute( 'INSERT INTO ' + recycle_table + ' ( remove_timestamp, remove_object_type, parent_path, loc_new, loc_old, loc_descr, remove_comment, remove_object_id ) VALUES(?,?,?,?,?,?,?,?) ', ( timestamp, object_type, parent_path, loc_new, loc_old, loc_descr, comment, id ) )
        remove_id = cur.lastrowid

        # Insert target object in table of removed objects
        removed_table = facility + '_Removed_Room'
        cur.execute( 'INSERT INTO ' + removed_table + ' ( id, room_num, old_num, location_type, description, remove_id ) VALUES(?,?,?,?,?,?) ', ( *row, remove_id ) )

        # Delete target object
        cur.execute( 'DELETE FROM ' + target_table + ' WHERE id=?', ( id, ) )
        self.removed_object_ids = [id]

        # Log activity
        facility_id = facility_name_to_id( facility )
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['removeLocation'], by, facility_id, before_summary, comment, 'Location', id  ) )

        conn.commit()


class addUser:
    def __init__(self, by, username, password, role, auth_facilities, status, first_name, last_name, email_address, organization, description, enterprise):
        open_database( enterprise )
        facility_id_csv = facility_names_to_ids( auth_facilities )
        new_user_id = dbCommon.add_interactive_user( cur, conn, by, username, password, role, True, ( status == 'Enabled' ), first_name, last_name, email_address, organization, description, facility_id_csv )

        self.messages = []
        self.selectors = []
        self.descendant_rows = []
        self.row = {}

        if new_user_id:
            self.row = userTableRow( user_id=new_user_id, enterprise=enterprise ).__dict__
        else:
            self.messages.append( "Username '" + username + "' is not available." )
            self.selectors.append( '#username' )


class updateUser:
    def __init__(self, by, username, oldPassword, password, role, auth_facilities, status, first_name, last_name, email_address, organization, description, enterprise):
        open_database( enterprise )

        # Get initial state of object for Activity log
        target_object_id = username_to_id( username )
        before_summary = summarize_object( 'User', target_object_id )

        self.messages = []
        self.selectors = []
        self.descendant_rows = []
        self.row = {}
        self.user = {}

        if password != None:
            if oldPassword != None:
                # Authenticate credentials to change password
                user = changePassword( by, username, oldPassword, password )
                if user.signInId == '':
                    self.messages.append( 'Old Password not valid.' )
                    self.selectors.append( '#oldPassword' )
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

            # Log activity
            cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
                VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['updateUser'], by, '', before_summary, summarize_object( 'User', target_object_id ), 'User', target_object_id  ) )

            conn.commit()

            # Retrieve the user
            cur.execute('SELECT * FROM User WHERE lower(username) = ?', (username.lower(),))
            user_row = cur.fetchone()

            # If we got a user row, load remaining user fields
            if user_row:
                self.user['username'] = user_row[1]
                role_id = user_row[3]
                cur.execute('SELECT role FROM Role WHERE id = ?', (role_id,))
                self.user['role'] = cur.fetchone()[0]
                self.user['user_description'] = user_row[4]
                if user_row[6]:
                    self.user['status'] = 'Enabled'
                else:
                    self.user['status'] = 'Disabled'
                self.user['first_name'] = user_row[7]
                self.user['last_name'] = user_row[8]
                self.user['email_address'] = user_row[9]
                self.user['organization'] = user_row[10]

                self.row = userTableRow( row=user_row, enterprise=enterprise ).__dict__


class removeUser:
    def __init__(self, by, id, enterprise):
        open_database( enterprise )

        # Get initial state of object for Activity log
        before_summary = summarize_object( 'User', id )

        # Delete the user
        cur.execute( 'DELETE FROM User WHERE id=?', ( id, ) )
        self.removed_object_ids = [id]

        # Log activity
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['removeUser'], by, '', before_summary, '', 'User', id  ) )

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


class restoreDropdowns:
    def __init__(self, enterprise, facility):

        open_database( enterprise )

        # Get voltages
        cur.execute('SELECT id, voltage FROM Voltage')
        rows = cur.fetchall()

        voltages = []
        for row in rows:
            voltages.append( { 'id': row[0], 'text': row[1]  } )

        self.voltages = natsort.natsorted( voltages, key=lambda x: x['text'] )

        # Get locations
        self.locations = get_location_dropdown( facility )

        # Get parents
        self.device_parents = get_distribution_dropdown( facility, ["'Circuit'"] )
        self.circuit_parents = get_distribution_dropdown( facility, ["'Panel'"] )
        self.transformer_parents = get_distribution_dropdown( facility, ["'Circuit'"] )
        self.panel_parents = get_distribution_dropdown( facility, ["'Circuit'", "'Transformer'"] )


class deviceDropdowns:
    def __init__(self, enterprise, facility):

        open_database( enterprise )

        # Get all potential sources
        self.sources = get_distribution_dropdown( facility, ["'Circuit'"] )

        # Get all locations
        self.locations = get_location_dropdown( facility )


class distributionDropdowns:
    def __init__(self, object_type, enterprise, facility):

        open_database( enterprise )

        # Get all potential parents
        if object_type == 'Circuit':
            aTypes = ["'Panel'"]
        else:
            aTypes = ["'Circuit'"]
            if object_type == 'Panel':
                aTypes.append( "'Transformer'" )

        self.parents = get_distribution_dropdown( facility, aTypes )

        # Get all locations
        self.locations = get_location_dropdown( facility )

        # Get all voltages
        cur.execute('SELECT id, voltage FROM Voltage')
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


class restoreRemovedObject:
    def __init__(self, by, id, parent_id, tail, room_id, enterprise, facility):

        open_database( enterprise )

        self.messages = []
        self.selectors = []

        # Get values representing object to be restored
        recycle_table = facility + '_Recycle'
        cur.execute( 'SELECT * FROM ' + recycle_table + ' WHERE id=?', ( id, ) );
        recycle_row = cur.fetchone()
        remove_object_type = recycle_row[2]

        # Handle according to removed object type
        if ( remove_object_type == 'Panel' ) or ( remove_object_type == 'Transformer' ) or ( remove_object_type == 'Circuit' ):
            remove_object_id = recycle_row[8]
            self.restore_distribution_object( by, id, remove_object_id, parent_id, tail, room_id, facility )
        elif remove_object_type == 'Device':
            self.restore_device( by, id, parent_id, room_id, facility )
        elif remove_object_type == 'Location':
            self.restore_location( by, id, facility )

        if len( self.messages ) == 0:
            # Clean up recyle bin
            cur.execute( 'DELETE FROM ' + recycle_table + ' WHERE id=?', ( id, ) );
            self.id = id

            conn.commit()


    def restore_distribution_object( self, by, id, remove_object_id, parent_id, tail, room_id, facility ):

        source_table = facility + '_Removed_Distribution'
        target_table = facility + '_Distribution'

        # Determine whether requested path is available
        ( test_id, restore_path, source ) = test_path_availability( target_table, parent_id, tail )

        if test_id:
            # Path already in use
            self.messages.append( "Path '" + restore_path + "' is not available." )
            self.selectors = [ '#parent_path', '#number', '#name' ];

        else:

            # Get root object from source table
            select_from_distribution( table=source_table, condition=(source_table + '.id=?'), params=(remove_object_id,) )
            removed_root_row = cur.fetchone()
            removed_path = removed_root_row[1]

            # Generate search result text
            description = removed_root_row[8]
            voltage = removed_root_row[13]
            ( loc_new, loc_old, loc_descr ) = get_location( room_id, facility )
            object_type = removed_root_row[14]
            search_result = dbCommon.make_search_result( source, voltage, loc_new, loc_old, loc_descr, object_type, description, tail )

            # Overwrite original values with new values in root row
            restore_root_row = list( removed_root_row )
            restore_root_row.pop()
            restore_root_row.pop()
            restore_root_row.pop()
            restore_root_row[1] = restore_path
            restore_root_row[3] = parent_id
            restore_root_row[7] = room_id
            restore_root_row[9] = tail
            restore_root_row[10] = search_result
            restore_root_row[11] = source

            # Restore root object at original ID
            cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (id, room_id, path, voltage_id, object_type_id, description, parent_id, tail, search_result, source)
                 VALUES (?,?,?,?,?,?,?,?,?,?)''', tuple( restore_root_row ) )

            # Get Distribution descendants
            select_from_distribution( table=source_table, condition=('remove_id=? AND ' + source_table + '.id<>?'), params=(id,remove_object_id) )
            descendants = cur.fetchall()

            # Update path, search result, and source of all descendants; restore at original IDs
            for desc in descendants:
                desc_path = desc[1]
                desc_room_id = desc[7]
                desc_description = desc[8]
                desc_tail = desc[9]
                desc_voltage = desc[13]
                desc_object_type = desc[14]
                ( desc_loc_new, desc_loc_old, desc_loc_descr ) = get_location( desc_room_id, facility )
                restore_desc_path = desc_path.replace( removed_path, restore_path, 1 )
                restore_desc_source = restore_desc_path.split( '.' )[-2]

                restore_desc_search_result = dbCommon.make_search_result( restore_desc_source, desc_voltage, desc_loc_new, desc_loc_old, desc_loc_descr, desc_object_type, desc_description, desc_tail )

                # Restore descendant object at original ID, with updated path, search result, and source
                restore_desc_row = list( desc )
                restore_desc_row.pop()
                restore_desc_row.pop()
                restore_desc_row.pop()
                restore_desc_row[1] = restore_desc_path
                restore_desc_row[10] = restore_desc_search_result
                restore_desc_row[11] = restore_desc_source
                cur.execute('''INSERT OR IGNORE INTO ''' + target_table + ''' (id, room_id, path, voltage_id, object_type_id, description, parent_id, tail, search_result, source)
                  VALUES (?,?,?,?,?,?,?,?,?,?)''', tuple( restore_desc_row ) )

            # Get descendant devices
            source_device_table = facility + '_Removed_Device'
            target_device_table = facility + '_Device'
            cur.execute( 'SELECT * FROM ' + source_device_table + ' WHERE remove_id=?', ( id, ) )
            devices = cur.fetchall()

            # Restore devices at original IDs
            for dev in devices:
                restore_dev_row = list( dev )
                restore_dev_row.pop()
                cur.execute( 'INSERT INTO ' + target_device_table + ' (id, room_id, parent_id, description, power, name ) VALUES (?,?,?,?,?,?) ', tuple( restore_dev_row ) )

            # Clean up restored objects from _Removed_ tables
            cur.execute( 'DELETE FROM ' + source_table + ' WHERE remove_id=?', ( id, ) );
            cur.execute( 'DELETE FROM ' + source_device_table + ' WHERE remove_id=?', ( id, ) );

            # Log activity
            facility_id = facility_name_to_id( facility )
            object_id = restore_root_row[0]
            cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
                VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['restore' + object_type], by, facility_id, '', summarize_object( object_type, object_id, facility ), object_type, object_id  ) )


    def restore_device( self, by, id, parent_id, room_id, facility ):

        # Determine source and target tables
        source_table = facility + '_Removed_Device'
        target_table = facility + '_Device'

        # Get fields from source table
        cur.execute( 'SELECT * FROM ' + source_table + ' WHERE remove_id=?', ( id, ) );
        source_row = cur.fetchone()

        # Copy source row and overwrite fields with updated values
        restore_row = list( source_row )
        restore_row.pop()
        restore_row[1] = room_id
        restore_row[2] = parent_id
        name = restore_row[5]
        restore_row[3] = make_device_description( name, room_id, facility )
        cur.execute( 'INSERT INTO ' + target_table + ' (id, room_id, parent_id, description, power, name ) VALUES (?,?,?,?,?,?) ', tuple( restore_row ) )

        # Clean up removed object
        cur.execute( 'DELETE FROM ' + source_table + ' WHERE remove_id=?', ( id, ) );

        # Log activity
        facility_id = facility_name_to_id( facility )
        object_id = restore_row[0]
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['restoreDevice'], by, facility_id, '', summarize_object( 'Device', object_id, facility ), 'Device', object_id  ) )


    def restore_location( self, by, id, facility ):

        # Determine source and target tables
        source_table = facility + '_Removed_Room'
        target_table = facility + '_Room'

        # Get fields from source table into list
        cur.execute( 'SELECT * FROM ' + source_table + ' WHERE remove_id=?', ( id, ) );
        source_row = cur.fetchone()
        source_row = list( source_row )

        # Restore object into target table
        restore_row = source_row
        restore_row.pop()
        restore_row = tuple( restore_row )
        cur.execute( 'INSERT INTO ' + target_table + ' (id, room_num, old_num, location_type, description) VALUES (?,?,?,?,?) ', restore_row )

        # Clean up removed object
        cur.execute( 'DELETE FROM ' + source_table + ' WHERE remove_id=?', ( id, ) );

        # Log activity
        facility_id = facility_name_to_id( facility )
        object_id = restore_row[0]
        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dbCommon.dcEventTypes['restoreLocation'], by, facility_id, '', summarize_object( 'Location', object_id, facility ), 'Location', object_id  ) )
