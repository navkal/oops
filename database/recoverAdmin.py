# Copyright 2017 Panel Spy.  All rights reserved.

import argparse
import context
import sqlite3
import dbCommon

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='recover admin user' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    conn = sqlite3.connect('database/' + args.enterprise + '/database.sqlite')
    cur = conn.cursor()
    cur.execute( 'UPDATE User SET password=?, force_change_password=? WHERE lower(username)=?', ( dbCommon.hash('recoverAdmin'), True, 'admin' ) )
    conn.commit()
