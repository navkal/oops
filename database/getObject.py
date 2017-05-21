# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='retrieve object from Panel Spy database' )
    parser.add_argument( '-t', '--table', dest='table', help='object table' )
    parser.add_argument( '-i', '--id', dest='id',  help='object id' )
    parser.add_argument( '-p', '--path', dest='path',  help='object path' )
    args = parser.parse_args()

    if args.path:
      selector = 'path="' + args.path + '"'
    elif args.id:
      selector = 'id=' + args.id
    else:
      selector = ''

    try:
      object = eval( 'sql.' + args.table + '( ' + selector + ' )' )
    except:
      dict = { 'Error': 'Could not retrieve [' + selector + '] from [' + args.table + '] table' }
    else:
      dict = object.__dict__

    printctl.on( )
    print( json.dumps( dict ) )
