# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='retrieve object from Panel Spy database' )
    parser.add_argument( '-t', '--type', dest='type', help='object type' )
    parser.add_argument( '-i', '--id', dest='id',  help='object id' )
    parser.add_argument( '-r', '--user_role', dest='user_role', help='user role' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    if args.type == 'Device':
        object_class = 'device'
    else:
        object_class = 'distributionObject'

    if args.id:
      sArgs = 'id=' + args.id + ', '
    else:
      sArgs = ''

    sArgs += 'user_role="' + args.user_role + '", '
    sArgs += 'enterprise="' + args.enterprise + '", '
    sArgs += 'facility="' + args.facility + '"'

    try:
      object = eval( 'sql.' + object_class + '( ' + sArgs + ' )' )
    except:
      dict = { 'Error': 'Could not retrieve [' + object_class + '] with attributes [' + sArgs + ']' }
    else:
      dict = object.__dict__

    printctl.on( )
    print( json.dumps( dict ) )
