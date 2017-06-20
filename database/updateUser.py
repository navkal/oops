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
    parser.add_argument( '-s', '--status', dest='status', help='role' )
    parser.add_argument( '-f', '--first_name', dest='first_name', help='role' )
    parser.add_argument( '-l', '--last_name', dest='last_name', help='role' )
    parser.add_argument( '-e', '--email_address', dest='email_address', help='role' )
    parser.add_argument( '-o', '--organization', dest='organization', help='role' )
    parser.add_argument( '-d', '--description', dest='description', help='description' )
    args = parser.parse_args()

    try:
        status = sql.updateUser( args.by, args.username, args.password, args.role, args.status, args.first_name, args.last_name, args.email_address, args.organization, args.description )
    except:
        dict = { 'Error': 'Failed to update user' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
