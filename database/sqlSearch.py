# Copyright 2018 Panel Spy.  All rights reserved.

import sqlite3


class search:
    def __init__(self, searchText, searchTargets=None, enterprise=None, facility=None):
        conn = sqlite3.connect('../database/' + enterprise + '/database.sqlite')
        cur = conn.cursor()

        if searchTargets:
            aTargets = searchTargets.split( ',' )
        else:
            aTargets = ['All']

        # Search Distribution paths
        if ( 'All' in aTargets ) or ( 'Path' in aTargets ):
            cur.execute('SELECT path, path FROM ' + facility + '_Distribution WHERE tail LIKE "%' + searchText + '%"')
            pathRows = cur.fetchall()
        else:
            pathRows = []


        # Search Distribution objects
        if ( 'All' in aTargets ) or ( 'Circuit' in aTargets ) or ( 'Panel' in aTargets ) or ( 'Transformer' in aTargets ):

            # Generate condition to select requested object types
            if ( 'All' in aTargets ) or ( ( 'Circuit' in aTargets ) and ( 'Panel' in aTargets ) and ( 'Transformer' in aTargets ) ):
                sWhere = ''
            else:
                aWhere = []
                if ( 'Panel' in aTargets ):
                    aWhere.append( "'Panel'" )
                if ( 'Transformer' in aTargets ):
                    aWhere.append( "'Transformer'" )
                if ( 'Circuit' in aTargets ):
                    aWhere.append( "'Circuit'" )

                sWhere = ' WHERE DistributionObjectType.object_type IN (' + ','.join( aWhere ) + ')'

            cur.execute(
              '''SELECT path, search_result
                  FROM
                    (SELECT
                        ''' + facility + '''_Distribution.path,
                        ''' + facility + '''_Distribution.search_result,
                        DistributionObjectType.object_type,
                        ''' + facility + '''_Distribution.tail AS tail,
                        ''' + facility + '''_Distribution.source AS source,
                        ''' + facility + '''_Distribution.description AS description,
                        Voltage.voltage AS voltage,
                        ''' + facility + '''_Room.room_num AS location,
                        ''' + facility + '''_Room.old_num AS location_old,
                        ''' + facility + '''_Room.description AS location_descr
                    FROM ''' + facility + '''_Distribution
                        LEFT JOIN DistributionObjectType ON ''' + facility + '''_Distribution.object_type_id = DistributionObjectType.id
                        LEFT JOIN Voltage ON ''' + facility + '''_Distribution.voltage_id = Voltage.id
                        LEFT JOIN ''' + facility + '''_Room ON ''' + facility + '''_Distribution.room_id = ''' + facility + '''_Room.id
                    '''
                    + sWhere +
                    ''')
                  WHERE
                      tail LIKE "%''' + searchText + '''%"
                      OR source LIKE "%''' + searchText + '''%"
                      OR voltage LIKE "%''' + searchText + '''%"
                      OR location LIKE "%''' + searchText + '''%"
                      OR location_old LIKE "%''' + searchText + '''%"
                      OR location_descr LIKE "%''' + searchText + '''%"
                      OR description LIKE "%''' + searchText + '''%"''' )

            distributionRows = cur.fetchall()
        else:
            distributionRows = []


        # Search devices
        if ( 'All' in aTargets ) or ( 'Device' in aTargets ):
            cur.execute(
              '''SELECT path, description
                  FROM
                  (SELECT
                    ''' + facility + '''_Distribution.path || "." || ''' + facility + '''_Device.id AS path,
                    ''' + facility + '''_Device.description,
                    ''' + facility + '''_Distribution.id,
                    ''' + facility + '''_Device.name AS name,
                    ''' + facility + '''_Room.room_num AS location,
                    ''' + facility + '''_Room.old_num AS location_old,
                    ''' + facility + '''_Room.description AS location_descr
                  FROM ''' + facility + '''_Device
                    LEFT JOIN ''' + facility + '''_Distribution ON ''' + facility + '''_Device.parent_id = ''' + facility + '''_Distribution.id
                    LEFT JOIN ''' + facility + '''_Room ON ''' + facility + '''_Device.room_id = ''' + facility + '''_Room.id)
                  WHERE
                    name LIKE "%''' + searchText + '''%"
                    OR location LIKE "%''' + searchText + '''%"
                    OR location_old LIKE "%''' + searchText + '''%"
                    OR location_descr LIKE "%''' + searchText + '''%"''')

            devRows = cur.fetchall()
        else:
            devRows = []

        # Concatenate all search results
        self.searchResults = pathRows + distributionRows + devRows

