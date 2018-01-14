# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='get values for dropdowns in restore dialog' )
    parser = context.add_context_args( parser )
    parser.add_argument( '-i', '--dist_object_id', dest='dist_object_id', help='distribution object ID' )
    parser.add_argument( '-t', '--object_type', dest='object_type', help='object type' )
    args = parser.parse_args()

    try:
        dropdowns = sql.restoreDropdowns( args.dist_object_id, args.object_type, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to get dropdowns for restore dialog in enterprise ' + args.enterprise + ', facility ' + args.facility }
    else:
        dict = dropdowns.__dict__

    printctl.on()
    print( json.dumps( dict ) )
