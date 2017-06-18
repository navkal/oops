# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='update user' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-u', '--username', dest='username', help='username' )
    parser.add_argument( '-p', '--password', dest='password', help='password' )
    parser.add_argument( '-r', '--role', dest='role', help='role' )
    parser.add_argument( '-d', '--description', dest='description', help='description' )
    parser.add_argument( '-f', '--force_change_password', dest='force_change_password', help='force change password' )
    args = parser.parse_args()

    try:
        status = sql.updateUser( args.by, args.username, args.password, args.role, args.description, args.force_change_password )
    except:
        dict = { 'Error': 'Failed to update user' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
