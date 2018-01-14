# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='retrieve object from Location Dictionary database' )
    parser.add_argument( '-o', '--table_object_type', dest='table_object_type', help='table object type' )
    parser.add_argument( '-r', '--user_role', dest='user_role', help='user role' )
    parser.add_argument( '-t', '--target_object_type', dest='target_object_type', nargs='?', default=None, help='log object type' )
    parser.add_argument( '-i', '--target_object_id', dest='target_object_id', nargs='?', default=None, help='log object id' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        table = sql.sortableTable( table_object_type=args.table_object_type, user_role=args.user_role, target_object_type=args.target_object_type, target_object_id=args.target_object_id, enterprise=args.enterprise, facility=args.facility )
    except:
        dict = { 'Error': 'Failed to retrieve sortable table' }
    else:
        dict = table.__dict__

    printctl.on()
    print( json.dumps( dict ) )
