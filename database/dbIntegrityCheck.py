import dbCommon
import pandas as pd

def check_database( conn, cur ):

    messages = []

    # Retrieve list of facilities
    cur.execute( 'SELECT facility_name, facility_fullname FROM Facility' )
    fac_rows = cur.fetchall()

    # Check all facilities
    for fac_row in fac_rows:

        facility_name = fac_row[0]
        facility_fullname = fac_row[1]

        messages += check_facility( conn, cur, facility_name, facility_fullname )

    print( ' num messages=' + str( len( messages ) ) )

def check_facility( conn, cur, facility_name, facility_fullname ):

    print( '==============check_facility=============>', facility_name, facility_fullname )

    messages = []

    df = pd.read_sql_query( 'SELECT * from ' + facility_name + '_Distribution', conn, index_col='id' )

    messages += check_distribution_root( cur, df, facility_fullname )
    messages += check_voltages( cur, df, facility_fullname )

    if len( messages ):
        print( messages )

    return messages


def check_distribution_root( cur, df, facility_fullname ):

    messages = []

    # Verify that there is exactly one root
    df_root = df[ df['parent_id'] == '' ]
    n_roots = len( df_root )
    if n_roots != 1:
        messages.append( make_message( facility_fullname, 'error', 'Distribution tree has ' + n_roots + ' roots' ) )

    # Verify that all paths descend from root
    root_path = df_root.iloc[0]['path']
    df_desc = df[ df['path'].str.startswith( root_path + '.' ) ]
    n_nodes = len( df )
    n_desc = len( df_desc )
    if n_nodes - n_desc != 1:
        messages.append( make_message( facility_fullname, 'error', "Not all paths descend from root '" + root_path + "'" ) )

    # Verify that root is a Panel
    root_object_type_id = df_root.iloc[0]['object_type_id']
    if root_object_type_id != dbCommon.object_type_to_id( cur, 'Panel' ):
        messages.append( make_message( facility_fullname, 'error', 'Root is not a Panel' ) )

    print( 'nodes:', n_nodes, ', roots:', n_roots, ', root path:', root_path, ', descendants:', n_desc )

    return messages


def check_voltages( cur, df, facility_fullname ):

    messages = []

    # Get high and low voltage IDs
    hi_id = dbCommon.voltage_to_id( cur, '277/480' )
    lo_id = dbCommon.voltage_to_id( cur, '120/208' )

    # Verify that all nodes have a voltage
    df_hi = df[ df['voltage_id'] == hi_id ]
    df_lo = df[ df['voltage_id'] == lo_id ]
    len_volt = len( df_hi ) + len( df_lo )
    len_no_volt = len( df ) - len_volt
    if len_no_volt:
        messages.append( make_message( facility_fullname, 'error', str( len_no_volt ) + ' elements have no voltage' ) )

    # Get all transformers
    df_trans = df[ df['object_type_id'] == dbCommon.object_type_to_id( cur, 'Transformer' ) ]

    # Iterate over list of transformers
    for index, row in df_trans.iterrows():

        path = row['path']

        # Verify that current transformer has low voltage
        if row['voltage_id'] != lo_id:
            messages.append( make_message( facility_fullname, 'error', "Transformer '" + path + "' has wrong voltage"  ) )

        descendant_prefix = path + '.'
        df_hi_descendants = df_hi[ df_hi['path'].str.startswith( descendant_prefix ) ]
        df_lo_descendants = df_lo[ df_lo['path'].str.startswith( descendant_prefix ) ]

        # Verify that current transformer has no high-voltage descendants
        num_hi_descendants = len( df_hi_descendants )
        if num_hi_descendants:
            messages.append( make_message( facility_fullname, 'error', "Transformer '" + path + "' has " + str( num_hi_descendants ) + ' high-voltage descendants'  ) )

        # Verify that transformer has at least one (low-voltage) descendant
        num_lo_descendants = len( df_lo_descendants )
        if num_lo_descendants == 0:
            messages.append( make_message( facility_fullname, 'warning', "Transformer '" + path + "' has " + str( num_lo_descendants ) + ' low-voltage descendants'  ) )

    # Extract list of low-voltage nodes that are not transformers
    df_lo_not_trans = df_lo[ df_lo['object_type_id'] != dbCommon.object_type_to_id( cur, 'Transformer' )]

    # Iterate over low-voltage nodes that are not transformers
    for index, row in df_lo_not_trans.iterrows():

        path = row['path']
        object_type = dbCommon.get_object_type( cur, row['object_type_id'] )

        # Verify that current low-voltage node has no descendant transformers
        descendant_prefix = path + '.'
        df_trans_descendants = df_trans[ df_trans['path'].str.startswith( descendant_prefix ) ]
        num_trans_descendants = len( df_trans_descendants )
        if num_trans_descendants:
            messages.append( make_message( facility_fullname, 'error', "Low-voltage " + object_type + " '" + path + "' has " + str( num_trans_descendants ) + ' Transformer descendants'  ) )

        # Verify that current low-voltage node descends from a Transformer
        ancestor_path = path
        found = False
        while '.' in ancestor_path and not found:
            ancestor_path = '.'.join( ancestor_path.split( '.' )[:-1] )
            df_found = df_trans[ df_trans['path'] == ancestor_path ]
            found = len( df_found ) > 0

        if not found:
            messages.append( make_message( facility_fullname, 'error', "Low-voltage " + object_type + " '" + path + "' has no Transformer ancestor"  ) )

    return messages


def make_message( facility_fullname, severity, text ):
    if severity in ( 'error', 'warning' ):
        return( { 'facility_fullname': facility_fullname, 'severity': severity, 'text': text } )
    else:
        raise ValueError( 'make_message(): Unknown message severity=' + severity )
