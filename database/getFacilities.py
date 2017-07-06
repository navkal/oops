# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='get facilities' )
    parser.add_argument( '-u', '--username', dest='username', help='username' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        facilities = sql.userFacilities( args.username, args.enterprise )
    except:
        dict = { 'Error': 'Failed to get facilities for user ' + args.username + ', enterprise ' + args.enterprise }
    else:
        dict = facilities.__dict__

    printctl.on()
    print( json.dumps( dict ) )
