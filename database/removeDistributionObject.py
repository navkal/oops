# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='remove distribution object' )
    parser.add_argument( '-b', '--by', dest='by', help='requested by username' )
    parser.add_argument( '-i', '--id', dest='id', help='object id' )
    parser.add_argument( '-c', '--comment', dest='comment', help='remove comment' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        status = sql.removeDistributionObject( args.by, args.id, args.comment, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to remove distribution object' }
    else:
        dict = status.__dict__

    printctl.on()
    print( json.dumps( dict ) )
