# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

#printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='add user' )
    parser.add_argument( '-u', '--username', dest='username', help='username' )
    parser.add_argument( '-p', '--password', dest='password', help='password' )
    parser.add_argument( '-r', '--role', dest='role', help='role' )
    args = parser.parse_args()

    try:
        user = sql.addUser( args.username, args.password, args.role )
    except:
        dict = { 'Error': 'Failed to add user' }
    else:
        dict = user.__dict__

    printctl.on()
    print( json.dumps( dict ) )
