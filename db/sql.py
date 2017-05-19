import sqlite3
import os
import time
from eventTypes import dcEventTypes

conn = sqlite3.connect('../db/database.sqlite')
cur = conn.cursor()


def make_device_label( name, room_id ):

    # Get location details
    if room_id:
        cur.execute('''SELECT room_num, old_num, description FROM Room WHERE id = ?''', (room_id,))
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


class device:
    def __init__(self,id=None,row=None):
        if not row:
            cur.execute(
              '''SELECT *
                  FROM
                    (SELECT
                        Device.id,
                        Device.room_id,
                        Device.parent_id,
                        Device.description,
                        Device.name,
                        CircuitObject.path,
                        CircuitObject.id
                    FROM Device
                        LEFT JOIN CircuitObject ON Device.parent_id = CircuitObject.id)
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
        self.label = make_device_label( self.name, self.room_id )

        #gets room where device is located
        if str( self.room_id ).isdigit():
            cur.execute('SELECT * FROM Room WHERE id = ?', (self.room_id,))
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

        cur.execute( "SELECT timestamp, username, event_type, description FROM Activity WHERE target_table = 'Device' AND target_column = 'id' AND target_value = ?", (self.id,) )
        self.events = cur.fetchall()


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

    def __init__(self,id=None,path=None,getkids=True):

        if id:
            cur.execute('SELECT * FROM CircuitObject WHERE id = ?', (id,))
        elif path:
            cur.execute('SELECT * FROM CircuitObject WHERE upper(path) = ?', (path.upper(),))
        else:
            cur.execute('SELECT * FROM CircuitObject WHERE path NOT LIKE "%.%"' )

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
        cur.execute('SELECT path FROM CircuitObject WHERE id = ?', (self.parent_id,))
        path_row = cur.fetchone()
        if path_row:
            self.parent_path = path_row[0]
        else:
            self.parent_path = ''

        # Get room information
        cur.execute('SELECT * FROM Room WHERE id = ?', (self.room_id,))
        room = cur.fetchone()
        self.loc_new = room[1]
        self.loc_old = room[2]
        self.loc_type = room[3]
        self.loc_descr = room[4]

        # Generate label
        self.label = make_cirobj_label( self )

        # Add image filename
        filename = 'images/' + self.path + '.jpg'
        if os.path.isfile( filename ):
            self.image = filename
        else:
            self.image = ''


        if getkids:

            # Retrieve children
            cur.execute('SELECT path FROM CircuitObject WHERE parent_id = ?', (self.id,))
            child_paths = cur.fetchall()
            self.children = []

            for i in range( len( child_paths ) ):
                child_path = child_paths[i][0]
                child = cirobj( path=child_path, getkids=False )
                filename = 'images/' + child_path + '.jpg'
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
                            Device.id AS device_id,
                            Device.parent_id,
                            CircuitObject.id,
                            CircuitObject.path
                        FROM Device
                            LEFT JOIN CircuitObject ON Device.parent_id = CircuitObject.id)
                        WHERE path = ?''', (self.path,) )

            dev_ids = cur.fetchall()
            self.devices = []
            for i in range( len (dev_ids) ):
                dev_id = dev_ids[i][0]
                dev = device( dev_id )
                self.devices.append( [ dev.id, dev.loc_new, dev.loc_old, dev.loc_descr, dev.description, dev.label ] )


        cur.execute( "SELECT timestamp, username, event_type, description FROM Activity WHERE target_table = 'CircuitObject' AND target_column = 'path' AND target_value = ?", (self.path,) )
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
    def __init__(self, searchText, searchTargets=None):

        if searchTargets:
            aTargets = searchTargets.split( ',' )
        else:
            aTargets = ['All']

        # Search CircuitObject paths
        if ( 'All' in aTargets ) or ( 'Path' in aTargets ):
            cur.execute('SELECT path, path FROM CircuitObject WHERE tail LIKE "%' + searchText + '%"')
            pathRows = cur.fetchall()
        else:
            pathRows = []


        # Search CircuitObject objects
        if ( 'All' in aTargets ) or ( 'Circuit' in aTargets ) or ( 'Panel' in aTargets ) or ( 'Transformer' in aTargets ):

            cur.execute(
              '''SELECT path, description
                  FROM
                    (SELECT
                        CircuitObject.object_type,
                        CircuitObject.description,
                        CircuitObject.path,
                        CircuitObject.tail AS tail,
                        CircuitObject.search_text AS search_text,
                        CircuitObject.source AS source,
                        Voltage.description AS voltage,
                        Room.room_num AS location,
                        Room.old_num AS location_old,
                        Room.description AS location_descr
                    FROM CircuitObject
                        LEFT JOIN Voltage ON CircuitObject.voltage_id = Voltage.id
                        LEFT JOIN Room ON CircuitObject.room_id = Room.id)
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

            descrRows = cur.fetchall()
        else:
            descrRows = []


        # Search devices
        if ( 'All' in aTargets ) or ( 'Device' in aTargets ):
            cur.execute(
              '''SELECT path, description
                  FROM
                  (SELECT
                    CircuitObject.path || "." || Device.id AS path,
                    Device.description,
                    CircuitObject.id,
                    Device.name as name,
                    Room.room_num AS location,
                    Room.old_num AS location_old,
                    Room.description AS location_descr
                  FROM Device
                    LEFT JOIN CircuitObject ON Device.parent_id = CircuitObject.id
                    LEFT JOIN Room ON Device.room_id = Room.id)
                  WHERE
                    name LIKE "%''' + searchText + '''%"
                    OR location LIKE "%''' + searchText + '''%"
                    OR location_old LIKE "%''' + searchText + '''%"
                    OR location_descr LIKE "%''' + searchText + '''%"''')

            devRows = cur.fetchall()
        else:
            devRows = []

        # Concatenate all search results
        self.searchResults = pathRows + descrRows + devRows


class sortableTable:
    def __init__(self, object_type):

        if object_type == 'device':
            # Retrieve all objects of requested type
            cur.execute(
              '''SELECT *
                  FROM
                    (SELECT
                        Device.id,
                        Device.room_id,
                        Device.parent_id,
                        Device.description,
                        Device.name,
                        CircuitObject.path,
                        CircuitObject.id
                    FROM Device
                        LEFT JOIN CircuitObject ON Device.parent_id = CircuitObject.id)''')



            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = device( row=obj )
                self.rows.append( row.__dict__ )

        elif object_type == 'location':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM Room')
            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = location( row=obj )
                self.rows.append( row.__dict__ )

        else:
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM CircuitObject WHERE upper(object_type) = ?', (object_type.upper(),))
            objects = cur.fetchall()

            # Add other fields to each row
            self.rows = []
            for obj in objects:
                row = sortableTableRow( obj )
                self.rows.append( row.__dict__ )

        print('found ' + str(len(self.rows)) + ' rows' )


class location:
    def __init__(self,id=None,row=None):
        if not row:
            cur.execute('SELECT * FROM Room WHERE id = ?', (id,))
            row = cur.fetchone()

        self.id = row[0]
        self.loc_new = row[1]
        self.loc_old = row[2]
        self.loc_type = row[3]
        self.loc_descr = row[4]

        cur.execute('SELECT COUNT(*) FROM Device WHERE room_id = ?', (self.id,))
        self.devices = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM CircuitObject WHERE room_id = ? AND object_type = "Panel"', (self.id,))
        self.panels = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM CircuitObject WHERE room_id = ? AND object_type = "Transformer"', (self.id,))
        self.transformers = cur.fetchone()[0]

        cur.execute('SELECT COUNT(*) FROM CircuitObject WHERE room_id = ? AND object_type = "Circuit"', (self.id,))
        self.circuits = cur.fetchone()[0]


class sortableTableRow:

    def __init__(self,row):

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

        cur.execute('SELECT * FROM Room WHERE id = ?', (self.room_id,))
        room = cur.fetchone()
        self.loc_new = room[1]
        self.loc_old = room[2]
        self.loc_type = room[3]
        self.loc_descr = room[4]

        cur.execute('SELECT COUNT(id) FROM CircuitObject WHERE parent_id = ?', (self.id,))
        self.children = cur.fetchone()[0]

        cur.execute(
          '''SELECT COUNT( device_id )
                FROM
                    (SELECT
                        Device.id AS device_id,
                        Device.parent_id,
                        CircuitObject.id,
                        CircuitObject.path
                    FROM Device
                        LEFT JOIN CircuitObject ON Device.parent_id = CircuitObject.id)
                    WHERE path = ?''', (self.path,) )

        self.devices = cur.fetchone()[0]



class saveNotes:
    def __init__(self, args):

        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description )
            VALUES (?,?,?,?,?,?,? )''', ( time.time(), 'bigBird', dcEventTypes['notes'], args.targetTable, args.targetColumn, args.targetValue, args.notes ) )

        conn.commit()

        self.status = 'success'
