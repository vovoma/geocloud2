<WFS_Capabilities version="1.0.0"
                  xmlns="http://www.opengis.net/wfs"
                  xmlns:<?php echo $gmlNameSpace; ?>="<?php echo $gmlNameSpaceUri; ?>"
                  xmlns:ogc="http://www.opengis.net/ogc"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://www.opengis.net/wfs http://wfs.plansystem.dk:80/geoserver/schemas/wfs/1.0.0/WFS-capabilities.xsd">
    <Service>
        <Name>MaplinkWebFeatureServer</Name>
        <Title><?php echo $gmlNameSpace; ?>s awesome WFS</Title>
        <Abstract>Mygeocloud.com</Abstract>
        <Keywords>WFS</Keywords>
        <OnlineResource><?php echo $thePath ?></OnlineResource>
        <Fees>NONE</Fees>
        <AccessConstraints>NONE</AccessConstraints>
    </Service>
    <Capability>
        <Request>
            <GetCapabilities>
                <DCPType>
                    <HTTP>
                        <Get onlineResource="<?php echo $thePath ?>?"/>
                    </HTTP>
                </DCPType>
                <DCPType>
                    <HTTP>
                        <Post onlineResource="<?php echo $thePath; ?>?"/>
                    </HTTP>
                </DCPType>
            </GetCapabilities>
            <DescribeFeatureType>
                <SchemaDescriptionLanguage>
                    <XMLSCHEMA/>
                </SchemaDescriptionLanguage>
                <DCPType>
                    <HTTP>
                        <Get onlineResource="<?php echo $thePath ?>?"/>
                    </HTTP>
                </DCPType>
                <DCPType>
                    <HTTP>
                        <Post onlineResource="<?php echo $thePath ?>?"/>
                    </HTTP>
                </DCPType>
            </DescribeFeatureType>
            <GetFeature>
                <ResultFormat>
                    <GML2/>
                </ResultFormat>
                <DCPType>
                    <HTTP>
                        <Get onlineResource="<?php echo $thePath ?>?"/>
                    </HTTP>
                </DCPType>
                <DCPType>
                    <HTTP>
                        <Post onlineResource="<?php echo $thePath ?>?"/>
                    </HTTP>
                </DCPType>
            </GetFeature>
            <Transaction>
                <DCPType>
                    <HTTP>
                        <Get onlineResource="<?php echo $thePath ?>?"/>
                    </HTTP>
                </DCPType>
                <DCPType>
                    <HTTP>
                        <Post onlineResource="<?php echo $thePath ?>?"/>
                    </HTTP>
                </DCPType>
            </Transaction>
            <!--<LockFeature>
				<DCPType>
					<HTTP>
						<Get onlineResource="<?php echo $thePath ?>"/>
					</HTTP>
				</DCPType>
				<DCPType>
					<HTTP>

						<Post onlineResource="<?php echo $thePath ?>"/>
					</HTTP>
				</DCPType>
	</LockFeature>
	<GetFeatureWithLock>
				<ResultFormat>
					<GML2/>
				</ResultFormat>
				<DCPType>

					<HTTP>
						<Get onlineResource="<?php echo $thePath ?>"/>
					</HTTP>
				</DCPType>
				<DCPType>
					<HTTP>
						<Post onlineResource="<?php echo $thePath ?>"/>
					</HTTP>
				</DCPType>
	</GetFeatureWithLock>-->
        </Request>
        <VendorSpecificCapabilities>
        </VendorSpecificCapabilities>
    </Capability>
    <?php
    $depth = 1;
    writeTag("open", null, "FeatureTypeList", null, True, True);
    $depth++;
    ?>
    <Operations>
        <Query/>
        <Insert/>
        <Update/>
        <Delete/>
        <!--<Lock/>-->
    </Operations>
    <?php
    $sql = "SELECT * from settings.getColumns('f_table_schema=''{$postgisschema}''','raster_columns.r_table_schema=''{$postgisschema}''') order by sort_id";

    $result = $postgisObject->execQuery($sql);
    if ($postgisObject->PDOerror) {
        makeExceptionReport($postgisObject->PDOerror);
    }
    while ($row = $postgisObject->fetchRow($result)) {
        if ($row['type'] != "RASTER" && $row['type'] != null) {

            if (!$srs) {
                $srsTmp = $row['srid'];
            } else {
                $srsTmp = $srs;
            }
            $latLongBoundingBoxSrs = "4326";

            $TableName = $row["f_table_name"];

            writeTag("open", null, "FeatureType", null, True, True);
            $depth++;

            writeTag("open", null, "Name", null, True, False);
            if ($gmlNameSpace) echo $gmlNameSpace . ":";
            echo $TableName;
            writeTag("close", null, "Name", null, False, True);

            writeTag("open", null, "Title", null, True, False);
            echo $row["f_table_title"];
            writeTag("close", null, "Title", null, False, True);


            writeTag("open", null, "Keywords", null, True, False);
            writeTag("close", null, "Keywords", null, False, True);


            writeTag("open", null, "SRS", null, True, False);
            echo "EPSG:" . $srsTmp;
            writeTag("close", null, "SRS", null, False, True);

            if ($row['f_geometry_column']) {
                $sql2 = "WITH bb AS (SELECT ST_astext(ST_Transform(ST_setsrid(ST_EstimatedExtent('" . $postgisschema . "', '" . $TableName . "', '" . $row['f_geometry_column'] . "')," . $row['srid'] . ")," . $latLongBoundingBoxSrs . ")) as geom) ";
                $sql2.= "SELECT ST_Xmin(ST_Extent(geom)) AS TXMin,ST_Xmax(ST_Extent(geom)) AS TXMax, ST_Ymin(ST_Extent(geom)) AS TYMin,ST_Ymax(ST_Extent(geom)) AS TYMax  FROM bb";
                $result2 = $postgisObject->prepare($sql2);
                try {
                    $result2->execute();
                    $row2 = $postgisObject->fetchRow($result2);
                    writeTag("open", null, "LatLongBoundingBox", array("minx" => $row2['txmin'], "miny" => $row2['tymin'], "maxx" => $row2['txmax'], "maxy" => $row2['tymax']), True, False);
                    writeTag("close", null, "LatLongBoundingBox", null, False, True);
                } catch (\PDOException $e) {
                    echo "<!--";
                    echo "WARNING: Optional LatLongBoundingBox could not be established for this layer.";
                    echo "-->";
                }
            }
            writeTag("open", null, "Abstract", null, True, False);
            echo $row["f_table_abstract"];
            writeTag("close", null, "Abstract", null, False, True);
            $depth--;
            writeTag("close", null, "FeatureType", null, True, True);
        }
    }
    $depth--;
    writeTag("close", null, "FeatureTypeList", null, True, True);
    ?>
    <ogc:Filter_Capabilities>
        <ogc:Spatial_Capabilities>
            <ogc:Spatial_Operators>
                <ogc:Disjoint/>
                <ogc:Equals/>
                <ogc:DWithin/>
                <ogc:Beyond/>
                <ogc:Intersect/>
                <ogc:Touches/>
                <ogc:Crosses/>
                <ogc:Within/>
                <ogc:Contains/>
                <ogc:Overlaps/>
                <ogc:BBOX/>
            </ogc:Spatial_Operators>
        </ogc:Spatial_Capabilities>
    </ogc:Filter_Capabilities>
</WFS_Capabilities>
