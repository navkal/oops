# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='add device' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-p', '--parent_id', dest='parent_id', help='parent id' )
    parser.add_argument( '-n', '--name', dest='name', help='device name' )
    parser.add_argument( '-r', '--room_id', dest='room_id', help='location id' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        status = sql.addDevice( args.by, args.parent_id, args.name, args.room_id, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to add device' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
