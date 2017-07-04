import hashlib
import time

def hash( text ):
    h = hashlib.md5()
    h.update( text.encode() )
    return h.hexdigest()

dcEventTypes = {
    'database': 'Database',
    'notes': 'Notes',
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
