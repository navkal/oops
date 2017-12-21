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

    print( '\nAnomalies found: ' + str( len( messages ) )  )

    for message in messages:
        print( '- ' + format_message( message ) )

    return messages


def check_facility( conn, cur, facility_name, facility_fullname ):

    print( "\n-- Facility '" + facility_fullname + "' --")

    messages = []

    df = pd.read_sql_query( 'SELECT * from ' + facility_name + '_Distribution', conn, index_col='id' )

    messages += check_distribution_root( cur, df, facility_fullname )
    messages += check_voltages( cur, df, facility_fullname )
    messages += check_three_phase( cur, df, facility_fullname )
    messages += check_distribution_parentage( cur, df, facility_fullname )
    messages += check_circuit_numbers( cur, df, facility_fullname )

    df_dev = pd.read_sql_query( 'SELECT * from ' + facility_name + '_Device', conn, index_col='id' )
    messages += check_device_parentage( cur, df, df_dev, facility_fullname )

    df_loc = pd.read_sql_query( 'SELECT * from ' + facility_name + '_Room', conn, index_col='id' )
    messages += check_location_refs( cur, df, df_dev, df_loc, facility_fullname )
    messages += check_location_names( cur, df_loc, facility_fullname )

    return messages


def check_distribution_root( cur, df, facility_fullname ):

    print( 'Checking Distribution root')

    messages = []

    # Verify that there is exactly one root
    df_root = df[ df['parent_id'] == '' ]
    n_roots = len( df_root )
    if n_roots != 1:
        messages.append( make_error_message( facility_fullname, 'Distribution', 'Tree', 'Has ' + str( n_roots ) + ' roots.' ) )

    # Verify that all paths descend from root
    root_path = df_root.iloc[0]['path']
    df_desc = df[ df['path'].str.startswith( root_path + '.' ) ]
    n_nodes = len( df )
    n_desc = len( df_desc )
    if n_nodes - n_desc != 1:
        messages.append( make_error_message( facility_fullname, 'Distribution', 'Tree', 'Some paths not descended from root ' + root_path + '.' ) )

    # Verify that root is a Panel
    root_object_type_id = df_root.iloc[0]['object_type_id']
    if root_object_type_id != dbCommon.object_type_to_id( cur, 'Panel' ):
        messages.append( make_error_message( facility_fullname, 'Distribution', 'Tree', 'Root is not a Panel.' ) )

    return messages


def check_voltages( cur, df, facility_fullname ):

    print( 'Checking Voltages')

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
        messages.append( make_error_message( facility_fullname, 'Distribution', 'Voltage', str( len_no_volt ) + ' elements have no Voltage setting.' ) )

    # Get all transformers
    df_trans = df[ df['object_type_id'] == dbCommon.object_type_to_id( cur, 'Transformer' ) ]

    # Iterate over list of transformers
    for index, row in df_trans.iterrows():

        path = row['path']

        # Verify that current transformer has low voltage
        if row['voltage_id'] != lo_id:
            messages.append( make_error_message( facility_fullname, 'Transformer', path, 'Has wrong Voltage setting.'  ) )

        descendant_prefix = path + '.'
        df_hi_descendants = df_hi[ df_hi['path'].str.startswith( descendant_prefix ) ]
        df_lo_descendants = df_lo[ df_lo['path'].str.startswith( descendant_prefix ) ]

        # Verify that current transformer has no high-voltage descendants
        num_hi_descendants = len( df_hi_descendants )
        if num_hi_descendants:
            messages.append( make_error_message( facility_fullname, 'Transformer', path, 'Has ' + str( num_hi_descendants ) + ' high-voltage descendants.'  ) )

        # Verify that transformer has at least one (low-voltage) descendant
        num_lo_descendants = len( df_lo_descendants )
        if num_lo_descendants == 0:
            messages.append( make_warning_message( facility_fullname, 'Transformer', path, 'Has no low-voltage descendants.'  ) )

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
            messages.append( make_error_message( facility_fullname, object_type, path, 'Low-voltage element has ' + str( num_trans_descendants ) + ' Transformer descendants.'  ) )

        # Verify that current low-voltage node descends from a Transformer
        ancestor_path = path
        found = False
        while '.' in ancestor_path and not found:
            ancestor_path = '.'.join( ancestor_path.split( '.' )[:-1] )
            df_found = df_trans[ df_trans['path'] == ancestor_path ]
            found = len( df_found ) > 0

        if not found:
            messages.append( make_error_message( facility_fullname, object_type, path, 'Low-voltage element not descended from Transformer.'  ) )

    return messages


def check_three_phase( cur, df, facility_fullname ):

    print( 'Checking three-phase connections')

    messages = []

    # Verify that no element has a Phase C parent without a Phase B parent
    df_c_only = df[ ( df['phase_b_parent_id'] == '' ) & ( df['phase_c_parent_id'] != '' ) ]
    n_c_only = len( df_c_only )
    if n_c_only:
        messages.append( make_error_message( facility_fullname, 'Distribution', 'Structure', str( n_c_only) + ' elements have a Phase C Parent but no Phase B Parent.'  ) )

    # Verify that no element has a Phase B parent without a Phase C parent
    df_b_only = df[ ( df['phase_b_parent_id'] != '' ) & ( df['phase_c_parent_id'] == '' ) ]
    n_b_only = len( df_b_only )
    if n_b_only:
        messages.append( make_warning_message( facility_fullname, 'Distribution', 'Structure', str( n_b_only) + ' elements have a Phase B Parent but no Phase C Parent.'  ) )

    # Verify that phase parents are siblings and circuits
    circuit_object_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )
    df_phase = df[ df['phase_b_parent_id'] != '' ]

    for index, row in df_phase.iterrows():

        # Verify that Phase B Parent is sibling of Parent
        granny_a_id = df.loc[ row['parent_id'] ]['parent_id']
        granny_b_id = df.loc[ row['phase_b_parent_id'] ]['parent_id']
        if granny_a_id != granny_b_id:
            messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Parent and Phase B Parent are not siblings.' ) )

        # Verify that Phase B Parent is a Circuit object
        b_parent_object_type_id = df.loc[ row['phase_b_parent_id'] ]['object_type_id']
        if b_parent_object_type_id != circuit_object_type_id:
            messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Phase B Parent is not a Circuit.' ) )

        if row['phase_c_parent_id']:

            # Verify that Phase C Parent is sibling of Parent
            granny_c_id = df.loc[ row['phase_c_parent_id'] ]['parent_id']
            if granny_a_id != granny_c_id:
                messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Parent and Phase C Parent are not siblings.' ) )

            # Verify that Phase C Parent is a Circuit object
            c_parent_object_type_id = df.loc[ row['phase_c_parent_id'] ]['object_type_id']
            if c_parent_object_type_id != circuit_object_type_id:
                messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Phase C Parent is not a Circuit.' ) )

    return messages


def check_distribution_parentage( cur, df, facility_fullname ):

    print( 'Checking Distribution parentage')

    messages = []

    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
    transformer_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )
    circuit_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )

    # Verify that all Panels have valid parent types and no unexpected siblings
    df_pan = df[ df['object_type_id'] == panel_type_id ]
    for index, row in df_pan.iterrows():
        parent_id = row['parent_id']

        if parent_id:
            # Validate parent type
            parent_type_id = df.loc[ parent_id ]['object_type_id']
            if parent_type_id not in [ transformer_type_id, circuit_type_id ]:
                messages.append( make_error_message( facility_fullname, 'Panel', row['path'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, parent_type_id ) + ').' ) )

            # If parent is a Circuit, verify that there are no siblings
            if parent_type_id == circuit_type_id:
                df_sibs = df.loc[ ( df['parent_id'] == parent_id ) ]
                if len( df_sibs ) > 1:
                    messages.append( make_error_message( facility_fullname, 'Panel', row['path'], 'Has unexpected siblings.' ) )

    # Verify that all Transformers have valid parent types and no siblings
    df_tran = df[ df['object_type_id'] == transformer_type_id ]
    for index, row in df_tran.iterrows():
        parent_id = row['parent_id']
        parent_type_id = df.loc[ parent_id ]['object_type_id']
        if parent_type_id != circuit_type_id:
            messages.append( make_error_message( facility_fullname, 'Transformer', row['path'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, parent_type_id ) + ').' ) )

        # Verify that there are no siblings
        df_sibs = df.loc[ ( df['parent_id'] == parent_id ) ]
        if len( df_sibs ) > 1:
            messages.append( make_error_message( facility_fullname, 'Transformer', row['path'], 'Has unexpected siblings.' ) )

    # Verify that all Circuits have valid parent types
    df_circ = df[ df['object_type_id'] == circuit_type_id ]
    for index, row in df_circ.iterrows():
        parent_id = row['parent_id']
        parent_type_id = df.loc[ parent_id ]['object_type_id']
        if parent_type_id != panel_type_id:
            messages.append( make_error_message( facility_fullname, 'Circuit', row['path'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, parent_type_id ) + ').' ) )

    return messages


def check_circuit_numbers( cur, df, facility_fullname ):

    print( 'Checking Circuit numbers')

    messages = []

    # Get all panels
    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
    df_pan = df[ df['object_type_id'] == panel_type_id ]

    # Iterate over panels
    for index, row in df_pan.iterrows():

        # Get all children of current panel
        df_kids = df[ df['parent_id'] == index ]

        # Initialize map of circuit numbers
        circuit_num_map = {}

        # Iterate over children of current panel
        for kid_index, kid_row in df_kids.iterrows():

            # Get leading part of tail
            tail = df_kids.loc[kid_index]['tail']
            tail_split = tail.split( '-' )
            leading = tail.split( '-' )[0]

            # If leading part is a number, look for duplicate in circuit number map
            if leading.isdigit():
                if leading in circuit_num_map:
                    messages.append( make_warning_message( facility_fullname, 'Panel', row['path'], 'Has multiple Circuits numbered ' + leading + '.'  ) )
                else:
                    circuit_num_map[leading] = tail

    return messages


def check_device_parentage( cur, df, df_dev, facility_fullname ):

    print( 'Checking Device parentage')

    messages = []

    circuit_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )

    for index, row in df_dev.iterrows():
        parent_id = row['parent_id']
        parent = df.loc[parent_id]
        parent_type_id = parent['object_type_id']

        if parent_type_id != circuit_type_id:
            messages.append( make_error_message( facility_fullname, 'Device', 'Connected to ' + parent['path'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, parent_type_id ) + ').' ) )

    return messages


def check_location_refs( cur, df, df_dev, df_loc, facility_fullname ):

    print( 'Checking Location references')

    messages = []

    for index, row in df_loc.iterrows():

        df_refs = df[ df['room_id'] == index ]
        n_refs = len( df_refs )

        if not n_refs:
            df_refs = df_dev[ df_dev['room_id'] == index ]
            n_refs = len( df_refs )

        if not n_refs:
            messages.append( make_warning_message( facility_fullname, 'Location', dbCommon.format_location( row['room_num'], row['old_num'], row['description'] ), 'Not referenced.' ) )

    return messages


def check_location_names( cur, df_loc, facility_fullname ):

    print( 'Checking Location names')

    messages = []

    for index, row in df_loc.iterrows():
        if row['room_num']:
            df_dup = df_loc[ df_loc['room_num'] == row['room_num'] ]
            n_dup = len( df_dup )

            if n_dup > 1:
                messages.append( make_warning_message( facility_fullname, 'Current Location', row['room_num'], 'Duplicates found.' ) )

        if row['old_num']:
            df_dup = df_loc[ df_loc['old_num'] == row['old_num'] ]
            n_dup = len( df_dup )

            if n_dup > 1:
                messages.append( make_warning_message( facility_fullname, 'Previous Location', row['old_num'], 'Duplicates found.' ) )

    return messages


'''
--> Reporting utilities -->
'''
def make_error_message( facility_fullname, affected_object_type, affected_object_descr, message_text ):
    return make_message( 'Error', facility_fullname, affected_object_type, affected_object_descr, message_text )

def make_warning_message( facility_fullname, affected_object_type, affected_object_descr, message_text ):
    return make_message( 'Warning', facility_fullname, affected_object_type, affected_object_descr, message_text )

def make_message( severity, facility_fullname, affected_object_type, affected_object_descr, message_text ):

    if severity not in ( 'Error', 'Warning' ):
        raise ValueError( 'make_message(): Unknown severity=' + severity )

    if affected_object_type not in ( 'Distribution', 'Panel', 'Transformer', 'Circuit', 'Device', 'Current Location', 'Previous Location', 'Location' ):
        raise ValueError( 'make_message(): Unknown affected_object_type: ' + affected_object_type )

    message = {
      'facility_fullname': facility_fullname,
      'severity': severity,
      'affected_object_type': affected_object_type,
      'affected_object_descr': affected_object_descr,
      'message_text': message_text
    }

    return message

def format_message( message ):
    formatted_message = ''

    formatted_message += message['facility_fullname'] + ': '
    formatted_message += '[' + message['severity'] + '] '
    formatted_message += '[' + message['affected_object_type'] + '] '
    formatted_message += '[' + message['affected_object_descr'] + '] '
    formatted_message += '[' + message['message_text'] + ']'

    return formatted_message
'''
<-- Reporting utilities <--
'''

