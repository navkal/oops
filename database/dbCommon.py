import hashlib

def hash( text ):
    h = hashlib.md5()
    h.update( text.encode() )
    return h.hexdigest()

dcEventTypes = {
    'database': 'database',
    'notes': 'notes'
}

def add_interactive_user( cur, username, password, role, description, require_password_change ):

    # Check whether username is unique
    cur.execute( '''SELECT username FROM User WHERE username = ?''', (username,))
    bUnique = len( cur.fetchall() ) == 0

    # If username is unique, add to table
    if bUnique:
        cur.execute( '''SELECT id FROM Role WHERE role = ?''', (role,))
        role_id = cur.fetchone()[0]
        cur.execute( '''INSERT OR IGNORE INTO User ( username, password, role_id, description, require_password_change ) VALUES (?,?,?,?,? )''', (username, hash(password), role_id, description, require_password_change) )

    return bUnique
