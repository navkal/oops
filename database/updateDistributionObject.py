# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='update distribution object' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-i', '--id', dest='id', help='distribution object id' )
    parser.add_argument( '-o', '--object_type', dest='object_type', help='distribution object type' )
    parser.add_argument( '-p', '--parent_id', dest='parent_id', help='parent id' )
    parser.add_argument( '-m', '--phase_b_parent_id', dest='phase_b_parent_id', help='phase b parent id' )
    parser.add_argument( '-n', '--phase_c_parent_id', dest='phase_c_parent_id', help='phase c parent id' )
    parser.add_argument( '-t', '--tail', dest='tail', help='tail segment of path' )
    parser.add_argument( '-w', '--three_phase', dest='three_phase', help='circuits grouped as three-phase' )
    parser.add_argument( '-r', '--room_id', dest='room_id', help='location id' )
    parser.add_argument( '-d', '--description', dest='description', help='distribution object description' )
    parser.add_argument( '-f', '--filename', dest='filename', help='image filename' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        status = sql.updateDistributionObject( args.by, args.id, args.object_type, args.parent_id, args.phase_b_parent_id, args.phase_c_parent_id, args.tail, args.three_phase, args.room_id, args.description, args.filename, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to update distribution object' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
