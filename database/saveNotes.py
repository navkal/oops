# Copyright 2017 Panel Spy.  All rights reserved.

import printctl
import argparse
import json

printctl.off()
import sql

if __name__ == '__main__':
    parser = argparse.ArgumentParser( description='save notes applicable to target table, column, and value' )
    parser.add_argument( '-t', '--table', dest='targetTable', help='target table' )
    parser.add_argument( '-c', '--column', dest='targetColumn',  help='target column' )
    parser.add_argument( '-v', '--value', dest='targetValue',  help='target value' )
    parser.add_argument( '-n', '--notes', dest='notes',  help='notes' )
    args = parser.parse_args()

    try:
        saveNotes = sql.saveNotes( args )
    except:
        dict = { 'status': 'Error: Failed to save notes at ' + str( args ) }
    else:
        dict = saveNotes.__dict__

    printctl.on( )
    print( json.dumps( dict ) )
