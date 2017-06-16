import hashlib
import time

def hash( text ):
    h = hashlib.md5()
    h.update( text.encode() )
    return h.hexdigest()

dcEventTypes = {
    'database': 'database',
    'notes': 'notes',
    'addUser': 'addUser',
    'updateUser': 'updateUser',
    'removeUser': 'removeUser'
}

def add_interactive_user( cur, conn, by, username, password, role, description ):

    # Check whether username is unique
    cur.execute( '''SELECT username FROM User WHERE lower(username) = ?''', (username.lower(),))
    bUnique = len( cur.fetchall() ) == 0

    # If username is unique, add to table
    if bUnique:
        cur.execute( '''SELECT id FROM Role WHERE role = ?''', (role,))
        role_id = cur.fetchone()[0]
        cur.execute( '''INSERT OR IGNORE INTO User ( username, password, role_id, description, force_change_password ) VALUES (?,?,?,?,? )''', (username, hash(password), role_id, description, True) )

        cur.execute('''INSERT INTO Activity ( timestamp, username, event_type, target_table, target_column, target_value, description )
            VALUES (?,?,?,?,?,?,? )''', ( time.time(), by, dcEventTypes['addUser'], 'User', 'username', username, "Add user '" + username + "'" ) )

        conn.commit()

    return bUnique
