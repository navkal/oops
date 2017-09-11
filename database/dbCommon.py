import hashlib
import time

def hash( text ):
    h = hashlib.md5()
    h.update( text.encode() )
    return h.hexdigest()

dcEventTypes = {
    'database': 'Database',
    'notes': 'Notes',
    'addDevice': 'Add Device',
    'updateDevice': 'Update Device',
    'addLocation': 'Add Location',
    'updateLocation': 'Update Location',
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

        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description )
            VALUES (?,?,?,?,?,?,? )''', ( time.time(), by, dcEventTypes['addUser'], 'User', 'username', username, "Add user '" + username + "'" ) )

        conn.commit()

    return bUnique


def append_location( text, location, location_old, location_descr, end_delimiter ):

    if location or location_old or location_descr:

        if location:
            text += ' ' + location

        if location_old:
            text += ' (' + location_old + ')'

        if location_descr:
            text += " '" + location_descr + "'"

        text += end_delimiter

    return text


def make_search_result( source, voltage, location, location_old, location_descr, object_type, descr_input, name ):

    bar = ' | '

    # Generate search result string, which must include all fragments matched by search operation
    search_result = ''

    if source:
        search_result += ' ' + source + bar

    if voltage:
        search_result += ' ' + voltage + 'V' + bar

    search_result = append_location( search_result, location, location_old, location_descr, bar )

    if object_type == 'Panel':
        # It's a panel; leave description empty and remove trailing bar delimiter
        description = ''
        if search_result:
            search_result = search_result[:-3]
    else:
        # Not a panel; use description field from CSV file
        description = descr_input
        if description:
            search_result += ' "' + description + '"'
        elif search_result:
            search_result = search_result[:-3]

    if search_result.strip():
        search_result = name + ':' + search_result
    else:
        search_result = name

    return ( search_result, description )
