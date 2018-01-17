# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='get enterprise build time' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        buildTime = sql.buildTime( args.enterprise )
    except:
        dict = { 'Error': 'Failed to get build time of enterprise ' + args.enterprise }
    else:
        dict = buildTime.__dict__

    printctl.on()
    print( json.dumps( dict ) )
