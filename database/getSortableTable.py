# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='retrieve object from Location Dictionary database' )
    parser.add_argument( '-o', '--object_type', dest='object_type', help='object type' )
    args = parser.parse_args()

    try:
        table = sql.sortableTable( args.object_type )
    except:
        dict = { 'Error': 'Failed to retrieve sortable table' }
    else:
        dict = table.__dict__

    printctl.on()
    print( json.dumps( dict ) )
