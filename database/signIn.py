# Copyright 2018 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='retrieve user' )
    parser.add_argument( '-u', '--username', dest='username', help='username' )
    parser.add_argument( '-p', '--password', dest='password', help='password' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        user = sql.signInUser( args.username, args.password, args.enterprise )
    except:
        dict = { 'Error': 'Failed to retrieve user' }
    else:
        dict = user.__dict__

    printctl.on()
    print( json.dumps( dict ) )
