# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='remove location' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-i', '--id', dest='id', help='id' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        status = sql.removeLocation( args.by, args.id, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to remove location' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
