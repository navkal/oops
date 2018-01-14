# Copyright 2018 Panel Spy.  All rights reserved.

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
    parser.add_argument( '-m', '--phase_b_parent_id', dest='phase_b_parent_id', help='phase b parent id' )
    parser.add_argument( '-n', '--phase_c_parent_id', dest='phase_c_parent_id', help='phase c parent id' )
    parser.add_argument( '-t', '--tail', dest='tail', help='tail of path' )
    parser.add_argument( '-r', '--room_id', dest='room_id', help='room id' )
    parser.add_argument( '-c', '--comment', dest='comment', help='restore comment' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        status = sql.restoreRemovedObject( args.by, args.id, args.parent_id, args.phase_b_parent_id, args.phase_c_parent_id, args.tail, args.room_id, args.comment, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to restore object' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
