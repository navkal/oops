# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='restore object from recycle bin to its original table' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-i', '--id', dest='id', help='recyle bin id' )
    parser.add_argument( '-p', '--parent_id', dest='parent_id', help='parent id' )
    parser.add_argument( '-t', '--tail', dest='tail', help='tail of path' )
    parser.add_argument( '-r', '--room_id', dest='room_id', help='room id' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        status = sql.restoreRemovedObject( args.by, args.id, args.parent_id, args.tail, args.room_id, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to restore object' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
