# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='remove user' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-u', '--username', dest='username', help='username' )
    args = parser.parse_args()

    try:
        status = sql.removeUser( args.by, args.username )
    except:
        dict = { 'Error': 'Failed to remove user' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
