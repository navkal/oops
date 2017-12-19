import hashlib
import time
import pandas as pd

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


def voltage_to_id( cur, voltage ):
    cur.execute( 'SELECT id FROM Voltage WHERE voltage=?', (voltage,) )
    row = cur.fetchone()
    voltage_id = row[0]
    return voltage_id


def check_database( conn, cur ):

    messages = []

    # Retrieve list of facilities
    cur.execute( 'SELECT facility_name, facility_fullname FROM Facility' )
    fac_rows = cur.fetchall()

    # Check all facilities
    for fac_row in fac_rows:

        facility_name = fac_row[0]
        facility_fullname = fac_row[1]

        messages += check_facility( conn, cur, facility_name, facility_fullname )

    print( ' num messages=' + str( len( messages ) ) )

def check_facility( conn, cur, facility_name, facility_fullname ):

    print( '==============check_facility=============>', facility_name, facility_fullname )

    messages = []

    df = pd.read_sql_query( 'SELECT * from ' + facility_name + '_Distribution', conn, index_col='id' )

    messages += check_distribution_root( cur, df, facility_fullname )
    messages += check_voltages( cur, df, facility_fullname )

    if len( messages ):
        print( messages )

    return messages


def check_distribution_root( cur, df, facility_fullname ):

    messages = []

    # Verify that there is exactly one root
    df_root = df[ df['parent_id'] == '' ]
    n_roots = len( df_root )
    if n_roots != 1:
        messages.append( make_message( facility_fullname, 'error', 'Distribution tree has ' + n_roots + ' roots' ) )

    # Verify that all paths descend from root
    root_path = df_root.iloc[0]['path']
    df_desc = df[ df['path'].str.startswith( root_path + '.' ) ]
    n_nodes = len( df )
    n_desc = len( df_desc )
    if n_nodes - n_desc != 1:
        messages.append( make_message( facility_fullname, 'error', "Not all paths descend from root '" + root_path + "'" ) )

    # Verify that root is a Panel
    root_object_type_id = df_root.iloc[0]['object_type_id']
    if root_object_type_id != object_type_to_id( cur, 'Panel' ):
        messages.append( make_message( facility_fullname, 'error', 'Root is not a Panel' ) )

    print( 'nodes:', n_nodes, ', roots:', n_roots, ', root path:', root_path, ', descendants:', n_desc )

    return messages


def check_voltages( cur, df, facility_fullname ):

    messages = []

    df_hi = df[ df['voltage_id'] == voltage_to_id( cur, '277/480' ) ]
    df_lo = df[ df['voltage_id'] == voltage_to_id( cur, '120/208' ) ]
    len_volt = len( df_hi ) + len( df_lo )
    len_no_volt = len( df ) - len_volt
    if len_no_volt:
        messages.append( make_message( facility_fullname, 'error', str( len_no_volt ) + ' nodes have no voltage' ) )

    df_trans = df[ df['object_type_id'] == object_type_to_id( cur, 'Transformer' ) ]
    for index, row in df_trans.iterrows():
        trans_path = row['path']
        print( index, trans_path )

        descendant_prefix = trans_path + '.'
        df_hi_descendants = df_hi[ df_hi['path'].str.startswith( descendant_prefix ) ]
        df_lo_descendants = df_lo[ df_lo['path'].str.startswith( descendant_prefix ) ]

        print( 'df_hi_descendants', len( df_hi_descendants ), 'df_lo_descendants', len( df_lo_descendants ) )

        # Verify that there are no high-voltage descendants of current transformer
        num_hi_descendants = len( df_hi_descendants )
        if num_hi_descendants:
            messages.append( make_message( facility_fullname, 'error', "Transformer '" + trans_path + "' has " + str( num_hi_descendants ) + ' high-voltage descendants'  ) )





    return messages




def make_message( facility_fullname, severity, text ):
    if severity in ( 'error', 'warning' ):
        return( { 'facility_fullname': facility_fullname, 'severity': severity, 'text': text } )
    else:
        raise ValueError( 'make_message(): Unknown message severity=' + severity )
