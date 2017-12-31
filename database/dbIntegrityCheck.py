import dbCommon
import pandas as pd
import time

one_facility = False
dc_tree = None
root_id = None


def check_database( conn, cur, facility=None ):

    start_time = time.time()

    messages = []

    if facility:
        # Retrieve requested facility
        cur.execute( 'SELECT facility_name, facility_fullname FROM Facility WHERE facility_name=?', (facility,) )
        global one_facility
        one_facility = True
    else:
        # Retrieve list of facilities
        cur.execute( 'SELECT facility_name, facility_fullname FROM Facility' )

    fac_rows = cur.fetchall()

    # Check each facility in list
    for fac_row in fac_rows:

        facility_name = fac_row[0]
        facility_fullname = fac_row[1]

        messages += check_facility( conn, cur, facility_name, facility_fullname )

    print( '\nAnomalies found: ' + str( len( messages ) )  )

    for message in messages:
        print( '- ' + format_message( message ) )

    print( '\nTotal elapsed seconds: ' + str( time.time() - start_time ) )

    return messages


def check_facility( conn, cur, facility_name, facility_fullname ):

    print( "\n-- Facility '" + facility_fullname + "' --")

    messages = []

    t = time.time()
    print( 'Loading distribution dataframe' )
    df = pd.read_sql_query( 'SELECT * FROM ' + facility_name + '_Distribution', conn, index_col='id' )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_distribution_root( cur, df, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_distribution_parentage( cur, df, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_distribution_siblings( cur, df, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_three_phase( cur, df, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Loading device dataframe' )
    df_dev = pd.read_sql_query( 'SELECT * FROM ' + facility_name + '_Device', conn )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_device_parentage( cur, df, df_dev, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Loading location dataframe' )
    df_loc = pd.read_sql_query( 'SELECT * FROM ' + facility_name + '_Room', conn )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_location_refs( cur, df, df_dev, df_loc, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_location_names( cur, df_loc, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Loading tree' )
    make_tree( cur, facility_name )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_voltages( cur, facility_name, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    messages += check_circuit_numbers( cur, facility_name, facility_fullname )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    return messages


def make_tree( cur, facility_name ):

    # Retrieve Distribution table
    cur.execute( 'SELECT id, parent_id, object_type_id, voltage_id, path, tail FROM ' + facility_name + '_Distribution' )
    rows = cur.fetchall()

    # Build dictionary representing Distribution tree
    global dc_tree
    global root_id

    dc_tree = {}
    for row in rows:
        dc_tree[row[0]] = { 'id': row[0], 'parent_id': row[1], 'object_type_id': row[2], 'voltage_id': row[3], 'path': row[4], 'tail': row[5], 'kid_ids':[] }

    for row in rows:
        if row[1]:
            dc_tree[row[1]]['kid_ids'].append( row[0] )
        else:
            root_id = row[0]


def check_distribution_root( cur, df, facility_fullname ):

    print( 'Checking distribution root')

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


def check_voltages( cur, facility_name, facility_fullname ):

    print( 'Checking voltages')

    messages = []

    transformer_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )
    hi_voltage_id = dbCommon.voltage_to_id( cur, '277/480' )
    lo_voltage_id = dbCommon.voltage_to_id( cur, '120/208' )

    # Traverse the tree
    messages += traverse_voltages( cur, dc_tree, root_id, hi_voltage_id, transformer_type_id, hi_voltage_id, lo_voltage_id, facility_fullname )

    return messages


def traverse_voltages( cur, subtree, subtree_root_id, expected_voltage_id, transformer_type_id, hi_voltage_id, lo_voltage_id, facility_fullname ):

    messages = []

    # Traverse kids of current subtree root
    for kid_id in subtree[subtree_root_id]['kid_ids']:

        kid = subtree[kid_id]
        path = kid['path']

        if kid['object_type_id'] == transformer_type_id:

            # Verify that this transformer is not descended from another transformer
            if expected_voltage_id == lo_voltage_id:
                messages.append( make_error_message( facility_fullname, 'Transformer', path, 'Is descended from another Transformer.'  ) )

            # Verify that this transformer has low voltage
            if kid['voltage_id'] != lo_voltage_id:
                messages.append( make_error_message( facility_fullname, 'Transformer', path, 'Has unexpected voltage ' + dbCommon.get_voltage( cur, kid['voltage_id'] ) + '.' ) )

            # Verify that this transformer has children
            if len( kid['kid_ids'] ) == 0:
                messages.append( make_warning_message( facility_fullname, 'Transformer', path, 'Has no children.'  ) )

            # Change expected voltage for descendants of this transformer
            new_expected_voltage_id = lo_voltage_id

        else:

            # Verify that this (non-transformer) object has expected voltage
            if kid['voltage_id'] != expected_voltage_id:
                messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, kid['object_type_id'] ), path, 'Has unexpected voltage ' + dbCommon.get_voltage( kid['voltage_id'] ) + '.'  ) )

            new_expected_voltage_id = expected_voltage_id

        # Traverse subtree rooted at current object
        messages += traverse_voltages( cur, subtree, kid_id, new_expected_voltage_id, transformer_type_id, hi_voltage_id, lo_voltage_id, facility_fullname )

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

    print( 'Checking distribution parentage')

    messages = []

    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
    transformer_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )
    circuit_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )

    # Verify that all Panels have valid parent types
    df_pan_no_root = df[ ( df['object_type_id'] == panel_type_id ) & ( df['parent_id'] != '' )]
    df_join = df_pan_no_root.join( df, on='parent_id', how='left', lsuffix='_of_panel', rsuffix='_of_parent' )
    df_wrong_parent_type = df_join[ ( df_join['object_type_id_of_parent'] != transformer_type_id ) & ( df_join['object_type_id_of_parent'] != circuit_type_id ) ]
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Panel', row['path_of_panel'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id_of_parent'] ) + ').' ) )

    # Verify that all Transformers have valid parent types
    df_tran = df[ df['object_type_id'] == transformer_type_id ]
    df_join = df_tran.join( df, on='parent_id', how='left', lsuffix='_of_transformer', rsuffix='_of_parent' )
    df_wrong_parent_type = df_join[ df_join['object_type_id_of_parent'] != circuit_type_id ]
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Transformer', row['path_of_transformer'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id_of_parent'] ) + ').' ) )

    # Verify that all Circuits have valid parent types
    df_circ = df[ df['object_type_id'] == circuit_type_id ]
    df_join = df_circ.join( df, on='parent_id', how='left', lsuffix='_of_circuit', rsuffix='_of_parent' )
    df_wrong_parent_type = df_join[ df_join['object_type_id_of_parent'] != panel_type_id ]
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Circuit', row['path_of_circuit'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id_of_parent'] ) + ').' ) )

    return messages


def check_distribution_siblings( cur, df, facility_fullname ):

    print( 'Checking distribution siblings')

    messages = []

    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
    transformer_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )
    circuit_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )

    df_n_sibs = df['parent_id'].value_counts().to_frame( 'n_sibs' )

    # Verify that Panels attached to Circuits have no siblings
    df_pan = df[ df['object_type_id'] == panel_type_id ]
    df_join = df_pan.join( df, on='parent_id', how='left', lsuffix='_of_panel', rsuffix='_of_parent' )
    df_join = df_join.join( df_n_sibs, on='parent_id_of_panel' )
    df_has_sibs = df_join[ ( df_join['object_type_id_of_parent'] == circuit_type_id ) & ( df_join['n_sibs'] > 1 ) ]
    for index, row in df_has_sibs.iterrows():
        messages.append( make_error_message( facility_fullname, 'Panel', row['path_of_panel'], 'Has unexpected siblings.' ) )

    # Verify that Transformers have no siblings
    df_tran = df[ df['object_type_id'] == transformer_type_id ]
    df_join = df_tran.join( df, on='parent_id', how='left', lsuffix='_of_transformer', rsuffix='_of_parent' )
    df_join = df_join.join( df_n_sibs, on='parent_id_of_transformer' )
    df_has_sibs = df_join[ df_join['n_sibs'] > 1 ]
    for index, row in df_has_sibs.iterrows():
        messages.append( make_error_message( facility_fullname, 'Panel', row['path_of_panel'], 'Has unexpected siblings.' ) )

    return messages


def check_circuit_numbers( cur, facility_name, facility_fullname ):

    print( 'Checking circuit numbers')

    messages = []

    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )

    # Traverse the tree
    messages += traverse_circuit_numbers( cur, dc_tree, root_id, panel_type_id, facility_fullname )

    return messages


def traverse_circuit_numbers( cur, subtree, subtree_root_id, panel_type_id, facility_fullname ):

    messages = []

    # Initialize dictionary of circuit numbers
    dc_circuit_numbers = {}

    # Traverse kids of current subtree root
    for kid_id in subtree[subtree_root_id]['kid_ids']:

        kid = subtree[kid_id]

        # If subtree root is a panel, look for duplicate numbers among kids
        if subtree[subtree_root_id]['object_type_id'] == panel_type_id:

            # Get number of current circuit
            number = kid['tail'].split( '-' )[0]

            if number:

                # Map circuit number to paths and tails
                if number in dc_circuit_numbers:
                    # Entry for this number already exists; append path and tail of current circuit
                    dc_circuit_numbers[number]['paths'].append( kid['path'] )
                    dc_circuit_numbers[number]['tails'].append( kid['tail'] )
                else:
                    # Create new entry for current circuit number
                    dc_circuit_numbers[number] = { 'paths': [ kid['path'] ], 'tails': [ kid['tail'] ] }

        # Traverse subtree rooted at current object
        messages += traverse_circuit_numbers( cur, subtree, kid_id, panel_type_id, facility_fullname )

    # Look for duplicate circuit numbers
    for number in dc_circuit_numbers:

        ls_paths = dc_circuit_numbers[number]['paths']
        ls_tails = dc_circuit_numbers[number]['tails']

        # If there are multiple paths corresponding to this circuit number, report warning
        n_paths = len( ls_paths )

        if n_paths > 1:
            for i_path in range( 0, n_paths ):
                path = ls_paths[i_path]
                ls_other_tails = ls_tails[:]
                del ls_other_tails[i_path]
                messages.append( make_warning_message( facility_fullname, 'Circuit', path, 'Originates at the same switch number as: ' + ', '.join( ls_other_tails ) + '.'  ) )

    return messages


def check_device_parentage( cur, df, df_dev, facility_fullname ):

    print( 'Checking device parentage')

    messages = []

    circuit_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )

    # Join dataframes to associate devices with their parents
    df_join = df_dev.join( df, on='parent_id', how='left', lsuffix='_of_device', rsuffix='_of_parent' )

    # Extract devices whose parents are of wrong type
    df_wrong_parent_type = df_join[ df_join['object_type_id'] != circuit_type_id ]

    # Report anomalies
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Device', 'Connected to ' + row['path'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id'] ) + ').' ) )

    return messages


def check_location_refs( cur, df, df_dev, df_loc, facility_fullname ):

    print( 'Checking location references')

    messages = []

    # Merge room table with distribution table
    df_no_dup = df.drop_duplicates( subset='room_id' )
    df_merge = pd.merge( df_loc, df_no_dup, how='left', left_on='id', right_on='room_id' )

    # Merge again, with device table
    df_no_dup = df_dev.drop_duplicates( subset='room_id' )
    df_merge = pd.merge( df_merge, df_no_dup, how='left', left_on='id', right_on='room_id' )

    # Extract locations with no references
    df_no_refs = df_merge[ df_merge['path'].isnull() & df_merge['name'].isnull() ]

    # Report locations that have no references
    for index, row in df_no_refs.iterrows():
        messages.append( make_warning_message( facility_fullname, 'Location', dbCommon.format_location( row['room_num'], row['old_num'], row['description_x'] ), 'Has no references.' ) )

    return messages


def check_location_names( cur, df_loc, facility_fullname ):

    print( 'Checking location names')

    messages = []

    messages += check_location_field( cur, df_loc, facility_fullname, 'room_num', 'Current Location' )
    messages += check_location_field( cur, df_loc, facility_fullname, 'old_num', 'Previous Location' )

    return messages


def check_location_field( cur, df_loc, facility_fullname, field, category ):

    messages = []

    # Get all locations with non-empty field value
    df_loc_new = df_loc[ df_loc[field] != '' ]

    # Count distinct field values. Index of series sr_counts is field value.
    sr_counts = df_loc_new[field].value_counts()

    # Extract series entries that represent duplicated field values
    sr_dups = sr_counts[sr_counts > 1]

    # Iterate over duplicates
    for index in sr_dups.index.values:
        messages.append( make_warning_message( facility_fullname, category, index, 'Occurs ' + str( sr_dups.loc[index] ) + ' times.' ) )

    return messages


'''
--> Reporting utilities -->
'''
def make_error_message( facility_fullname, affected_category, affected_element, anomaly_descr ):
    return make_message( 'Error', facility_fullname, affected_category, affected_element, anomaly_descr )

def make_warning_message( facility_fullname, affected_category, affected_element, anomaly_descr ):
    return make_message( 'Warning', facility_fullname, affected_category, affected_element, anomaly_descr )

def make_message( severity, facility_fullname, affected_category, affected_element, anomaly_descr ):

    if severity not in ( 'Error', 'Warning' ):
        raise ValueError( 'make_message(): Unknown severity=' + severity )

    if affected_category not in ( 'Distribution', 'Panel', 'Transformer', 'Circuit', 'Device', 'Current Location', 'Previous Location', 'Location' ):
        raise ValueError( 'make_message(): Unknown affected_category: ' + affected_category )

    message = {
      'facility_fullname': '' if one_facility else facility_fullname,
      'severity': severity,
      'affected_category': affected_category,
      'affected_element': affected_element,
      'anomaly_descr': anomaly_descr
    }

    return message

def format_message( message ):
    formatted_message = ''

    formatted_message += message['facility_fullname'] + ': '
    formatted_message += '[' + message['severity'] + '] '
    formatted_message += '[' + message['affected_category'] + '] '
    formatted_message += '[' + message['affected_element'] + '] '
    formatted_message += '[' + message['anomaly_descr'] + ']'

    return formatted_message
'''
<-- Reporting utilities <--
'''

