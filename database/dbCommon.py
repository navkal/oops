import hashlib
import time

def hash( text ):
    h = hashlib.md5()
    h.update( text.encode() )
    return h.hexdigest()


dcEventTypes = {
    'database': 'Database',
    'addNote': 'Add Note',
    'addCircuit': 'Add Circuit',
    'updateCircuit': 'Update Circuit',
    'removeCircuit': 'Remove Circuit',
    'restoreCircuit': 'Restore Circuit',
    'addPanel': 'Add Panel',
    'updatePanel': 'Update Panel',
    'removePanel': 'Remove Panel',
    'restorePanel': 'Restore Panel',
    'addTransformer': 'Add Transformer',
    'updateTransformer': 'Update Transformer',
    'removeTransformer': 'Remove Transformer',
    'restoreTransformer': 'Restore Transformer',
    'addDevice': 'Add Device',
    'updateDevice': 'Update Device',
    'removeDevice': 'Remove Device',
    'restoreDevice': 'Restore Device',
    'addLocation': 'Add Location',
    'updateLocation': 'Update Location',
    'removeLocation': 'Remove Location',
    'restoreLocation': 'Restore Location',
    'addUser': 'Add User',
    'updateUser': 'Update User',
    'removeUser': 'Remove User'
}


def add_interactive_user( cur, conn, by, username, password, role, force_change_password=True, enabled=True, first_name='', last_name='', email_address='', organization='', description='', facility_ids='' ):

    # Check whether username is unique
    cur.execute( '''SELECT username FROM User WHERE lower(username) = ?''', (username.lower(),))
    bUnique = len( cur.fetchall() ) == 0

    # If username is unique, add to table
    if bUnique:
        cur.execute( '''SELECT id FROM Role WHERE role = ?''', (role,))
        role_id = cur.fetchone()[0]
        cur.execute( '''INSERT OR IGNORE INTO User ( username, password, role_id, description, force_change_password, enabled, first_name, last_name, email_address, organization, facility_ids )
            VALUES (?,?,?,?,?,?,?,?,?,?,? )''', (username, hash(password), role_id, description, force_change_password, enabled, first_name, last_name, email_address, organization, facility_ids) )
        target_object_id = cur.lastrowid

        cur.execute('''INSERT INTO Activity ( timestamp, event_type, username, facility_id, event_target, event_result, target_object_type, target_object_id )
            VALUES (?,?,?,?,?,?,?,?)''', ( time.time(), dcEventTypes['addUser'], by, '', '', summarize_user( cur, target_object_id ), 'User', target_object_id  ) )

        conn.commit()
    else:
        target_object_id = None

    return target_object_id


def format_location( location, location_old, location_descr ):

    format = ''

    if location:
        format += location + ' '
    if location_old:
        format += '(' + location_old + ') '
    if location_descr:
        format += "'" + location_descr + "'"
    format = format.strip();

    return format;


def append_location( text, location, location_old, location_descr, end_delimiter ):

    if location or location_old or location_descr:
        text += ' ' + format_location( location, location_old, location_descr ) + end_delimiter

    return text


def format_device_description( name, location, location_old, location_descr ):

    desc = append_location( '', location, location_old, location_descr, '' )

    if desc:
        desc = name + ':' + desc
    else:
        desc = name

    return desc


def make_search_result( source, voltage, location, location_old, location_descr, description, name ):

    bar = ' | '

    # Generate search result string, which must include all fragments matched by search operation
    search_result = ''

    if source:
        search_result += ' ' + source + bar

    if voltage:
        search_result += ' ' + voltage + 'V' + bar

    search_result = append_location( search_result, location, location_old, location_descr, bar )

    if description:
        search_result += ' "' + description + '"' + bar

    if search_result:
        search_result = search_result[:-3]

    if search_result.strip():
        search_result = name + ':' + search_result
    else:
        search_result = name

    # Remove excess spaces
    search_result = ' '.join( search_result.split() )

    return search_result


object_type_map = None
def object_type_to_id( cur, object_type ):

    global object_type_map

    # Load type-to-id map for future use
    if not object_type_map:

        object_type_map = {}

        cur.execute( 'SELECT * FROM DistributionObjectType' )
        rows = cur.fetchall()
        for row in rows:
            object_type_map[row[1]] = str( row[0] )

    # Return ID corresponding to the supplied object type
    return object_type_map[object_type]


def path_to_id( cur, path, sFacility='' ):
    cur.execute('SELECT id FROM ' + sFacility + '_Distribution WHERE path = ?', ( path, ))
    index = cur.fetchone()
    return str( index[0] )


object_type_id_map = None
def get_object_type( cur, object_type_id ):

    global object_type_id_map

    # Load type-to-id map for future use
    if not object_type_id_map:

        object_type_id_map = {}

        cur.execute( 'SELECT * FROM DistributionObjectType' )
        rows = cur.fetchall()
        for row in rows:
            object_type_id_map[str(row[0])] = row[1]

    return object_type_id_map[ str( object_type_id ) ]


def get_role( cur, role_id ):
    cur.execute( 'SELECT role FROM Role WHERE id = ?', (role_id,))
    return cur.fetchone()[0]


def summarize_user( cur, id ):
    id = str( id )
    cur.execute( 'SELECT * FROM User WHERE id = ?', (id,))
    row = cur.fetchone()
    username = row[1]
    role_id = row[3]
    description = row[4]
    enabled = row[6]
    first_name = row[7]
    last_name = row[8]
    email_address = row[9]
    organization = row[10]
    facility_ids = row[11]

    username = username + ':'

    role = ' ' + get_role( cur, role_id )

    if facility_ids:
        facility_ids = facility_ids.split( ',' )
        facilities = []
        for facility_id in facility_ids:
            cur.execute('SELECT facility_fullname FROM Facility WHERE id = ?', (facility_id,))
            facilities.append( cur.fetchone()[0] )
        facilities = ' [' + ', '.join( facilities ) + ']'
    else:
        facilities = ''

    if enabled:
        enabled = ' Enabled'
    else:
        enabled = ' Disabled'

    full_name = ( first_name + ' ' + last_name ).strip()
    if full_name:
        full_name = " '" + full_name + "'"

    if email_address:
        email_address = ' ' + email_address

    if organization:
        organization = ' [' + organization + ']'

    if description:
        description = ' [' + description + ']'

    summary = username + role + facilities + enabled + full_name + email_address + organization + description

    return summary


voltage_map = None
def voltage_to_id( cur, voltage ):

    global voltage_map

    if not voltage_map:

        voltage_map = {}

        cur.execute( 'SELECT * FROM Voltage' )
        rows = cur.fetchall()

        for row in rows:
            voltage_map[row[1]] = row[0]

    return voltage_map[voltage]


def get_voltage( cur, voltage_id ):
    cur.execute( 'SELECT voltage FROM Voltage WHERE id = ?', (voltage_id,) )
    voltage = cur.fetchone()[0]
    return voltage
