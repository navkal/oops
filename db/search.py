# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='search' )
    parser.add_argument( '-s', '--searchText', dest='searchText',  help='search text' )
    args = parser.parse_args()

    try:
      searchResults = sql.search( args.searchText );
    except:
      dict = { 'Error': 'Failed to search for [' + args.searchText + '] in [' + args.table + '] table' }
    else:
      dict = searchResults.__dict__

    printctl.on()
    print( json.dumps( dict ) )
