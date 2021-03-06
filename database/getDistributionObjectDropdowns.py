# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='get values for dropdowns in Panel, Transformer, or Circuit edit dialog' )
    parser.add_argument( '-i', '--id', dest='id', help='object ID' )
    parser.add_argument( '-t', '--object_type', dest='object_type', help='object type' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        dropdowns = sql.distributionDropdowns( args.id, args.object_type, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to get dropdowns for edit dialog of Distribution object id=<' + args.id + '> type=<' + args.object_type + '> in enterprise ' + args.enterprise + ', facility ' + args.facility }
    else:
        dict = dropdowns.__dict__

    printctl.on()
    print( json.dumps( dict ) )
