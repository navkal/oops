import hashlib

def hash( text ):
    h = hashlib.md5()
    h.update( text.encode() )
    return h.hexdigest()

dcEventTypes = {
    'database': 'database',
    'notes': 'notes'
}
