# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='retrieve image filename corresponding to specified path' )
    parser.add_argument( '-p', '--path', dest='path', help='object path' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        filename = sql.imageFilename( args.path, args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to retrieve image filename' }
    else:
        dict = filename.__dict__

    printctl.on()
    print( json.dumps( dict ) )
