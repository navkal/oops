import dbCommon
import pandas as pd
import time
import re

one_facility = False
name_pattern = re.compile( r'^[a-zA-Z0-9\-_]+$' )


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

    # Sort the messages by severity, etc.
    messages = sorted( messages, key=lambda x: [ x['facility_fullname'], x['severity'], x['affected_category'], x['affected_element'], x['anomaly_descr'] ] )

    print( '\nAnomalies found: ' + str( len( messages ) )  )

    i = 1
    for message in messages:
        print( str(i) + '. ' +  format_message( message ) )
        i += 1

    print( '\nTotal elapsed seconds: ' + str( time.time() - start_time ) )

    return messages


def check_facility( conn, cur, facility_name, facility_fullname ):

    print( "\n-- Facility '" + facility_fullname + "' --")

    messages = []

    t = time.time()
    print( 'Loading tree' )
    try:
        ( dc_tree, root_id, dc_orphans ) = make_tree( cur, facility_name )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while loading tree.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Loading dataframes' )
    try:
        df = pd.read_sql_query( 'SELECT * FROM ' + facility_name + '_Distribution', conn, index_col='id' )
        df_dev = pd.read_sql_query( 'SELECT * FROM ' + facility_name + '_Device', conn )
        df_loc = pd.read_sql_query( 'SELECT * FROM ' + facility_name + '_Room', conn )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while loading dataframes.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking distribution root')
    try:
        messages += check_distribution_root( cur, df, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking distribution root.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking distribution connectivity')
    try:
        messages += check_distribution_connectivity( cur, dc_tree, root_id, dc_orphans, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking distribution connectivity.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking distribution hierarchy')
    try:
        messages += check_distribution_hierarchy( cur, df, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking distribution hierarchy.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking distribution paths')
    try:
        messages += check_distribution_paths( cur, dc_tree, root_id, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking distribution paths.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking three-phase connections')
    try:
        messages += check_three_phase( cur, df, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking three-phase connections.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking distribution siblings')
    try:
        messages += check_distribution_siblings( cur, df, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking distribution siblings.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking voltages')
    try:
        messages += check_voltages( cur, dc_tree, root_id, facility_name, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking voltages.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking circuit numbers')
    try:
        messages += check_circuit_numbers( cur, dc_tree, root_id, facility_name, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking circuit numbers.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking device hierarchy')
    try:
        messages += check_device_hierarchy( cur, df, df_dev, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking device hierarchy.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking location references')
    try:
        messages += check_location_refs( cur, df, df_dev, df_loc, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking location references.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    t = time.time()
    print( 'Checking location names')
    try:
        messages += check_location_names( cur, df_loc, facility_fullname )
    except:
        messages.append( make_alert_message( facility_fullname, 'Facility', 'Data', 'Exception while checking location names.' ) )
    print( 'Elapsed seconds:', time.time() - t, '\n' )

    return messages


def make_tree( cur, facility_name ):

    # Retrieve Distribution table
    cur.execute( 'SELECT id, parent_id, object_type_id, voltage_id, path, source, tail FROM ' + facility_name + '_Distribution' )
    rows = cur.fetchall()

    # Build dictionary representing Distribution tree
    dc_tree = {}
    dc_orphans = {}

    for row in rows:
        dc_tree[row[0]] = { 'id': row[0], 'parent_id': row[1], 'object_type_id': row[2], 'voltage_id': row[3], 'path': row[4], 'source': row[5], 'tail': row[6], 'connected': False, 'kid_ids':[] }

    for row in rows:
        id = row[0]
        parent_id = row[1]

        if parent_id:
            if parent_id in dc_tree:
                dc_tree[parent_id]['kid_ids'].append( id )
            else:
                dc_orphans[id] = { 'object_type_id': row[2], 'path': row[4] }
        else:
            root_id = id

    return dc_tree, root_id, dc_orphans


def check_distribution_root( cur, df, facility_fullname ):

    messages = []

    df_root = df[ df['parent_id'] == '' ]

    # Verify that there is exactly one root
    n_roots = len( df_root )
    if n_roots == 1:

        # Verify that root is a Panel
        root_object_type_id = df_root.iloc[0]['object_type_id']
        if root_object_type_id != dbCommon.object_type_to_id( cur, 'Panel' ):
            messages.append( make_error_message( facility_fullname, 'Distribution', 'Tree', 'Root is not a Panel.' ) )

    else:
        messages.append( make_error_message( facility_fullname, 'Distribution', 'Tree', 'Has ' + str( n_roots ) + ' roots.' ) )

    return messages


def check_distribution_connectivity( cur, dc_tree, root_id, dc_orphans, facility_fullname ):

    messages = []

    # Identify all connected nodes
    messages += traverse_connectivity( cur, dc_tree, root_id, facility_fullname )

    # Build list of disconnected paths
    dc_discon = { 'path': [], 'object_type_id': [] }

    for id in dc_tree:
        node = dc_tree[id]
        if not node['connected']:
            dc_discon['path'].append( node['path'] )
            dc_discon['object_type_id'].append( node['object_type_id'] )

    # Identify relative roots among disconnected paths
    df_discon_roots = pd.DataFrame( dc_discon )
    loop_expression = ( path for path in dc_discon['path'] if len( df_discon_roots ) > 1 )
    for path in loop_expression:
        df_discon_roots = df_discon_roots[ ~ df_discon_roots['path'].str.startswith( path + '.' ) ]

    # Add non-root orphans found during loading of tree
    for orphan_id in dc_orphans:
        df_discon_roots = df_discon_roots.append( dc_orphans[orphan_id], ignore_index=True )

    df_discon_roots = df_discon_roots.drop_duplicates( subset=['path'] )

    # Report disconnected roots and non-root orphans
    for index, row in df_discon_roots.iterrows():
        messages.append( make_critical_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Disconnected from Distribution tree.'  ) )

    return messages


def traverse_connectivity( cur, dc_tree, subtree_root_id, facility_fullname ):

    messages = []

    subtree_root = dc_tree[subtree_root_id]

    # Indicate that this node is connected to the tree
    subtree_root['connected'] = True

    # Traverse kids of current subtree root
    for kid_id in subtree_root['kid_ids']:
        messages += traverse_connectivity( cur, dc_tree, kid_id, facility_fullname )

    return messages


def check_distribution_hierarchy( cur, df, facility_fullname ):

    messages = []

    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
    transformer_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )
    circuit_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )

    # Verify that all Panels have valid parent types
    df_pan_no_root = df[ ( df['object_type_id'] == panel_type_id ) & ( df['parent_id'] != '' )]
    df_join = df_pan_no_root.join( df, on='parent_id', how='inner', lsuffix='_of_panel', rsuffix='_of_parent' )
    df_wrong_parent_type = df_join[ ( df_join['object_type_id_of_parent'] != transformer_type_id ) & ( df_join['object_type_id_of_parent'] != circuit_type_id ) ]
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Panel', row['path_of_panel'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id_of_parent'] ) + ').' ) )

    # Verify that all Transformers have valid parent types
    df_tran = df[ df['object_type_id'] == transformer_type_id ]
    df_join = df_tran.join( df, on='parent_id', how='inner', lsuffix='_of_transformer', rsuffix='_of_parent' )
    df_wrong_parent_type = df_join[ df_join['object_type_id_of_parent'] != circuit_type_id ]
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Transformer', row['path_of_transformer'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id_of_parent'] ) + ').' ) )

    # Verify that all Circuits have valid parent types
    df_circ = df[ df['object_type_id'] == circuit_type_id ]
    df_join = df_circ.join( df, on='parent_id', how='inner', lsuffix='_of_circuit', rsuffix='_of_parent' )
    df_wrong_parent_type = df_join[ df_join['object_type_id_of_parent'] != panel_type_id ]
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Circuit', row['path_of_circuit'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id_of_parent'] ) + ').' ) )

    return messages


def check_distribution_paths( cur, dc_tree, root_id, facility_fullname ):

    messages = []

    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )
    transformer_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )

    messages += traverse_distribution_paths( cur, dc_tree, root_id, panel_type_id, transformer_type_id, facility_fullname )

    return messages


def traverse_distribution_paths( cur, dc_tree, subtree_root_id, panel_type_id, transformer_type_id, facility_fullname ):

    messages = []

    # Extract fields of subtree root
    subtree_root = dc_tree[subtree_root_id]
    subtree_root_path = subtree_root['path']
    subtree_root_tail = subtree_root['tail']
    subtree_root_type_id = subtree_root['object_type_id']

    # Extract name from subtree root tail
    a_tail = subtree_root_tail.split( '-', maxsplit=1 )
    name = None

    if a_tail[0].isdigit():
        if len( a_tail ) > 1:
            name = a_tail[1]
    else:
        name = subtree_root_tail

    # Verify name syntax
    if ( name != None ):
        if name.isdigit():
            messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, subtree_root_type_id ), subtree_root_path, 'Name ' + "'" + name + "'" + ' must contain at least one non-digit character.'  ) )
        elif name_pattern.match( name ) == None:
            messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, subtree_root_type_id ), subtree_root_path, 'Name ' + "'" + name + "'" + ' can contain only alphanumeric, hyphen, and underscore characters.'  ) )

    # Verify that name is present for Panel or Transformer
    if ( subtree_root_type_id in ( panel_type_id, transformer_type_id ) ) and ( name == None ):
        messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, subtree_root_type_id ), subtree_root_path, 'Has no name.'  ) )

    # Traverse kids of current subtree root
    for kid_id in subtree_root['kid_ids']:

        kid = dc_tree[kid_id]

        a_kid_path = kid['path'].split( '.' )
        kid_path_leading = '.'.join( a_kid_path[:-1] )
        kid_path_trailing = '.'.join( a_kid_path[-1:] )

        # Verify that leading portion of kid path matches parent path
        if kid_path_leading != subtree_root_path:
            messages.append( make_critical_message( facility_fullname, dbCommon.get_object_type( cur, kid['object_type_id'] ), kid['path'], 'Path is not child of ' + "'" + subtree_root_path + "'" + '.'  ) )

        # Verify that kid tail matches trailing element of kid path
        if kid_path_trailing != kid['tail']:
            messages.append( make_critical_message( facility_fullname, dbCommon.get_object_type( cur, kid['object_type_id'] ), kid['path'], 'Tail '  + "'" + kid['tail'] + "'" + ' does not match last segment of path.'  ) )

        # Verify that kid source matches parent tail
        if kid['source'] != subtree_root_tail:
            messages.append( make_critical_message( facility_fullname, dbCommon.get_object_type( cur, kid['object_type_id'] ), kid['path'], 'Source '  + "'" + kid['source'] + "'" + ' does not match tail of parent ' + "'" + subtree_root_tail + "'" + '.'  ) )

        messages += traverse_distribution_paths( cur, dc_tree, kid_id, panel_type_id, transformer_type_id, facility_fullname )

    return messages


def check_three_phase( cur, df, facility_fullname ):

    messages = []

    # Verify that no element has a Phase C parent without a Phase B parent
    df_c_only = df[ ( df['phase_b_parent_id'] == '' ) & ( df['phase_c_parent_id'] != '' ) ]
    for index, row in df_c_only.iterrows():
        messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Has a Phase C Parent but no Phase B Parent.'  ) )

    # Verify that no element has a Phase B parent without a Phase C parent
    df_b_only = df[ ( df['phase_b_parent_id'] != '' ) & ( df['phase_c_parent_id'] == '' ) ]
    for index, row in df_b_only.iterrows():
        messages.append( make_warning_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Has a Phase B Parent but no Phase C Parent.'  ) )

    # Verify that phase parents are siblings and circuits
    circuit_object_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )
    df_phase = df[ df['phase_b_parent_id'] != '' ]

    for index, row in df_phase.iterrows():
        parent_id = row['parent_id']
        phase_b_parent_id = row['phase_b_parent_id']
        phase_c_parent_id = row['phase_c_parent_id']

        parents_found = True

        if parent_id not in df.index.values:
            parents_found = False

        if phase_b_parent_id not in df.index.values:
            messages.append( make_critical_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Phase B Parent not found in Distribution tree.' ) )
            parents_found = False

        if phase_c_parent_id and ( phase_c_parent_id not in df.index.values ):
            messages.append( make_critical_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Phase C Parent not found in Distribution tree.' ) )
            parents_found = False

        if parents_found:

            # Verify that Phase B Parent is sibling of Parent
            granny_a_id = df.loc[parent_id]['parent_id']
            granny_b_id = df.loc[phase_b_parent_id]['parent_id']
            if granny_a_id != granny_b_id:
                messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Parent and Phase B Parent are not siblings.' ) )

            # Verify that Phase B Parent is a Circuit object
            b_parent_object_type_id = df.loc[phase_b_parent_id]['object_type_id']
            if b_parent_object_type_id != circuit_object_type_id:
                messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Phase B Parent is not a Circuit.' ) )

            if phase_c_parent_id:

                # Verify that Phase C Parent is sibling of Parent
                granny_c_id = df.loc[phase_c_parent_id]['parent_id']
                if granny_a_id != granny_c_id:
                    messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Parent and Phase C Parent are not siblings.' ) )

                # Verify that Phase C Parent is a Circuit object
                c_parent_object_type_id = df.loc[phase_c_parent_id]['object_type_id']
                if c_parent_object_type_id != circuit_object_type_id:
                    messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, row['object_type_id'] ), row['path'], 'Phase C Parent is not a Circuit.' ) )

    return messages


def check_distribution_siblings( cur, df, facility_fullname ):

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
        messages.append( make_error_message( facility_fullname, 'Transformer', row['path_of_transformer'], 'Has unexpected siblings.' ) )

    return messages


def check_voltages( cur, dc_tree, root_id, facility_name, facility_fullname ):

    messages = []

    transformer_type_id = dbCommon.object_type_to_id( cur, 'Transformer' )
    hi_voltage_id = dbCommon.voltage_to_id( cur, '277/480' )
    lo_voltage_id = dbCommon.voltage_to_id( cur, '120/208' )

    # Traverse the tree
    messages += traverse_voltages( cur, dc_tree, root_id, hi_voltage_id, transformer_type_id, hi_voltage_id, lo_voltage_id, facility_fullname )

    return messages


def traverse_voltages( cur, dc_tree, subtree_root_id, expected_voltage_id, transformer_type_id, hi_voltage_id, lo_voltage_id, facility_fullname ):

    messages = []

    # Traverse kids of current subtree root
    for kid_id in dc_tree[subtree_root_id]['kid_ids']:

        kid = dc_tree[kid_id]
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
                messages.append( make_error_message( facility_fullname, dbCommon.get_object_type( cur, kid['object_type_id'] ), path, 'Has unexpected voltage ' + dbCommon.get_voltage( cur, kid['voltage_id'] ) + '.'  ) )

            new_expected_voltage_id = expected_voltage_id

        # Traverse subtree rooted at current object
        messages += traverse_voltages( cur, dc_tree, kid_id, new_expected_voltage_id, transformer_type_id, hi_voltage_id, lo_voltage_id, facility_fullname )

    return messages


def check_circuit_numbers( cur, dc_tree, root_id, facility_name, facility_fullname ):

    messages = []

    panel_type_id = dbCommon.object_type_to_id( cur, 'Panel' )

    # Traverse the tree
    messages += traverse_circuit_numbers( cur, dc_tree, root_id, panel_type_id, facility_fullname )

    return messages


def traverse_circuit_numbers( cur, dc_tree, subtree_root_id, panel_type_id, facility_fullname ):

    messages = []

    # Initialize dictionary of circuit numbers
    dc_circuit_numbers = {}

    # Traverse kids of current subtree root
    for kid_id in dc_tree[subtree_root_id]['kid_ids']:

        kid = dc_tree[kid_id]

        # If subtree root is a panel, look for duplicate numbers among kids
        if dc_tree[subtree_root_id]['object_type_id'] == panel_type_id:

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
        messages += traverse_circuit_numbers( cur, dc_tree, kid_id, panel_type_id, facility_fullname )

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


def check_device_hierarchy( cur, df, df_dev, facility_fullname ):

    messages = []

    circuit_type_id = dbCommon.object_type_to_id( cur, 'Circuit' )

    # Join dataframes to associate devices with their parents
    df_join = df_dev.join( df, on='parent_id', how='left', lsuffix='_of_device', rsuffix='_of_parent' )

    # Extract devices whose parents are of wrong type
    df_wrong_parent_type = df_join[ df_join['object_type_id'] != circuit_type_id ]

    # Report anomalies
    for index, row in df_wrong_parent_type.iterrows():
        messages.append( make_error_message( facility_fullname, 'Device', "'" + row['name'] + "'" + ' on ' + row['path'], 'Has parent of wrong type (' + dbCommon.get_object_type( cur, row['object_type_id'] ) + ').' ) )

    return messages


def check_location_refs( cur, df, df_dev, df_loc, facility_fullname ):

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
def make_alert_message( facility_fullname, affected_category, affected_element, anomaly_descr ):
    return make_message( 'Alert', facility_fullname, affected_category, affected_element, anomaly_descr )

def make_critical_message( facility_fullname, affected_category, affected_element, anomaly_descr ):
    return make_message( 'Critical', facility_fullname, affected_category, affected_element, anomaly_descr )

def make_error_message( facility_fullname, affected_category, affected_element, anomaly_descr ):
    return make_message( 'Error', facility_fullname, affected_category, affected_element, anomaly_descr )

def make_warning_message( facility_fullname, affected_category, affected_element, anomaly_descr ):
    return make_message( 'Warning', facility_fullname, affected_category, affected_element, anomaly_descr )

def make_message( severity, facility_fullname, affected_category, affected_element, anomaly_descr ):

    if severity not in ( 'Alert', 'Critical', 'Error', 'Warning' ):
        raise ValueError( 'make_message(): Unknown severity=' + severity )

    if affected_category not in ( 'Facility', 'Distribution', 'Panel', 'Transformer', 'Circuit', 'Device', 'Current Location', 'Previous Location', 'Location' ):
        raise ValueError( 'make_message(): Unknown affected_category: ' + affected_category )

    message = {
      'severity': severity,
      'facility_fullname': '' if one_facility else facility_fullname,
      'affected_category': affected_category,
      'affected_element': affected_element,
      'anomaly_descr': anomaly_descr
    }

    return message

def format_message( message ):
    formatted_message = ''

    formatted_message += message['severity'] + ': '
    formatted_message += '[' + message['facility_fullname'] + '] '
    formatted_message += '[' + message['affected_category'] + '] '
    formatted_message += '[' + message['affected_element'] + '] '
    formatted_message += '[' + message['anomaly_descr'] + ']'

    return formatted_message
'''
<-- Reporting utilities <--
'''
