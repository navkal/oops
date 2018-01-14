# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='get all facilities in enterprise' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        facilities = sql.allFacilities( args.enterprise )
    except:
        dict = { 'Error': 'Failed to get all facilities in enterprise ' + args.enterprise }
    else:
        dict = facilities.__dict__

    printctl.on()
    print( json.dumps( dict ) )
