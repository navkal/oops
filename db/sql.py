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
            label += location
        if location_old:
            label += ' (' + location_old + ')'
        if location_descr:
            label += " '" + location_descr + "'"

    return label



class device:
    def __init__(self,id=None,row=None):
        if not row:
            cur.execute('SELECT * FROM Device WHERE id = ?', (id,))
            row = cur.fetchone()

        self.id = row[0]
        self.room_id = row[1]
        self.panel_id = row[2]
        self.description = row[3]
        self.parent_path = row[5] # For tree structure
        self.source_path = row[5] # For properties display
        self.name = row[6]
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
        print("panel_id:",self.panel_id)
        print("description:", self.description)
        print("parent_path:",self.parent_path)
        print("loc_new:", self.loc_new)
        print("loc_old:", self.loc_old)

    def get_main_display(self):
        return {'ID': self.id,
                'Room ID': self.room_id,
                'Panel ID': self.panel_id,
                'Description':self.description,
                'Parent Path': self.parent_path,
                'Location New': self.loc_new,
                'Location Old': self.loc_old}


class cirobj:

    def __init__(self,id=None,path=None):
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
        self.parent_path = row[7]
        self.label = row[9]
        self.source = row[11]

        # Get room information
        cur.execute('SELECT * FROM Room WHERE id = ?', (self.room_id,))
        room = cur.fetchone()
        self.loc_new = room[1]
        self.loc_old = room[2]
        self.loc_type = room[3]
        self.loc_descr = room[4]

        # Add image filename
        filename = 'images/' + self.path + '.jpg'
        if os.path.isfile( filename ):
            self.image = filename
        else:
            self.image = ''

        # Retrieve children
        cur.execute('SELECT id, path, label, object_type FROM CircuitObject WHERE parent = ?', (self.path,))
        self.children = cur.fetchall()

        # Append child image filenames
        for i in range( len( self.children ) ):
            filename = 'images/' + self.children[i][1] + '.jpg'
            if os.path.isfile( filename ):
                self.children[i] = self.children[i] + ( filename, )
            else:
                self.children[i] = self.children[i] + ('',)

        cur.execute('SELECT id FROM Device WHERE parent = ?', (self.path,))
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
    def __init__(self, searchText):

        cur.execute('SELECT path, path FROM CircuitObject WHERE tail LIKE "%' + searchText + '%"')
        pathRows = cur.fetchall()

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

        cur.execute(
          '''SELECT path, description
              FROM
              (SELECT
                Device.parent || "." || Device.id AS path,
                Device.description,
                Device.name,
                Device.room_id,
                Room.room_num AS location,
                Room.old_num AS location_old,
                Room.description AS location_descr
              FROM Device
                LEFT JOIN Room ON Device.room_id = Room.id)
              WHERE
                name LIKE "%''' + searchText + '''%"
                OR location LIKE "%''' + searchText + '''%"
                OR location_old LIKE "%''' + searchText + '''%"
                OR location_descr LIKE "%''' + searchText + '''%"''')

        devRows = cur.fetchall()

        self.searchResults = pathRows + descrRows + devRows


class sortableTable:
    def __init__(self, object_type):

        if object_type == 'device':
            # Retrieve all objects of requested type
            cur.execute('SELECT * FROM Device')
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
        self.parent_path = row[7]
        self.source = row[11]

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

        cur.execute('SELECT COUNT(id) FROM CircuitObject WHERE parent = ?', (self.path,))
        self.children = cur.fetchone()[0]

        cur.execute('SELECT COUNT(id) FROM Device WHERE parent = ?', (self.path,))
        self.devices = cur.fetchone()[0]



class saveNotes:
    def __init__(self, args):

        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description )
            VALUES (?,?,?,?,?,?,? )''', ( time.time(), 'bigBird', dcEventTypes['notes'], args.targetTable, args.targetColumn, args.targetValue, args.notes ) )

        conn.commit()

        self.status = 'success'
