# Copyright 2017 Panel Spy.  All rights reserved.

import sqlite3
import dbCommon
import uuid
import json

conn = sqlite3.connect('database/database.sqlite')
cur = conn.cursor()
cur.execute( 'UPDATE User SET password=?, force_change_password=? WHERE lower(username)=?', ( dbCommon.hash('recoverAdmin'), True, 'admin' ) )
conn.commit()

print( json.dumps( { 'username': 'admin', 'signInId': '', 'forceChangePassword': True } ) )
