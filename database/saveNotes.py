# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json
import context

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='save notes applicable to target table, column, and value' )
    parser.add_argument( '-u', '--username', dest='username', help='username' )
    parser.add_argument( '-t', '--object_type', dest='object_type', help='object type' )
    parser.add_argument( '-i', '--object_id', dest='object_id',  help='object id' )
    parser.add_argument( '-n', '--notes', dest='notes',  help='notes' )
    parser = context.add_context_args( parser )
    args = parser.parse_args()

    try:
        saveNotes = sql.saveNotes( args )
    except:
        dict = { 'status': 'Error: Failed to save notes at ' + str( args ) }
    else:
        dict = saveNotes.__dict__

    printctl.on( )
    print( json.dumps( dict ) )
