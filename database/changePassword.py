# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='change password' )
    parser.add_argument( '-u', '--username', dest='username', help='username' )
    parser.add_argument( '-p', '--password', dest='password', help='password' )
    args = parser.parse_args()

    try:
        user = sql.changePassword( args.username, args.password )
    except:
        dict = { 'Error': 'Failed to change password' }
    else:
        dict = user.__dict__

    printctl.on()
    print( json.dumps( dict ) )
