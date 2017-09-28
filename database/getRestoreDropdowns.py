# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='get values for dropdowns in restore dialog' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        dropdowns = sql.restoreDropdowns( args.enterprise, args.facility )
    except:
        dict = { 'Error': 'Failed to get dropdowns for restore dialog in enterprise ' + args.enterprise + ', facility ' + args.facility }
    else:
        dict = dropdowns.__dict__

    printctl.on()
    print( json.dumps( dict ) )
