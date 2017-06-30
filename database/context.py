# Copyright 2017 Panel Spy.  All rights reserved.

def add_context_args( parser ):
    parser.add_argument( '-y', '--enterprise', dest='enterprise', help='enterprise' )
    parser.add_argument( '-z', '--facility', dest='facility', help='facility', default='')
    return parser
