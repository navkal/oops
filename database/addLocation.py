# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='add location' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-l', '--location', dest='location', help='location' )
    parser.add_argument( '-o', '--old', dest='old_location', help='old location' )
    parser.add_argument( '-d', '--description', dest='description', help='description' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        status = sql.addLocation( args.by, args.location, args.old_location, args.description, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to add location' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
