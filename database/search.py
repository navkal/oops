# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='search' )
    parser.add_argument( '-s', '--searchText', dest='searchText',  help='search text' )
    parser.add_argument( '-t', '--searchTargets', dest='searchTargets',  help='comma-separated list of search targets' )
    args = parser.parse_args()

    try:
      searchResults = sql.search( args.searchText, args.searchTargets );
    except:
      dict = { 'Error': 'Failed to search for [' + args.searchText + '] in targets [' + args.searchTargets + ']' }
    else:
      dict = searchResults.__dict__

    printctl.on()
    print( json.dumps( dict ) )
