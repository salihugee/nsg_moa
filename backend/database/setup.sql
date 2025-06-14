-- Setup script for NSG MOA database
-- Run this as postgres superuser

-- Create database
DROP DATABASE IF EXISTS nsg_moa;
CREATE DATABASE nsg_moa
    WITH 
    ENCODING = 'UTF8'
    LC_COLLATE = 'en_US.UTF-8'
    LC_CTYPE = 'en_US.UTF-8'
    TEMPLATE = template0;

-- Connect to the new database
\c nsg_moa

-- Create PostGIS extension
CREATE EXTENSION IF NOT EXISTS postgis;
CREATE EXTENSION IF NOT EXISTS postgis_topology;
CREATE EXTENSION IF NOT EXISTS fuzzystrmatch;
CREATE EXTENSION IF NOT EXISTS postgis_tiger_geocoder;

-- Create user with limited privileges
DROP USER IF EXISTS nsg_moa_user;
CREATE USER nsg_moa_user WITH PASSWORD 'NSG_m0a_2025_Secure!';

-- Grant privileges
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO nsg_moa_user;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO nsg_moa_user;

GRANT CONNECT ON DATABASE nsg_moa TO nsg_moa_user;
GRANT USAGE ON SCHEMA public TO nsg_moa_user;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO nsg_moa_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO nsg_moa_user;

-- Grant PostGIS-specific privileges
GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO nsg_moa_user;
GRANT USAGE ON SCHEMA topology TO nsg_moa_user;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA topology TO nsg_moa_user;

-- Create spatial indexes
CREATE INDEX idx_farmers_location ON farmers USING GIST (location);
CREATE INDEX idx_farms_boundaries ON farms USING GIST (boundaries);
CREATE INDEX idx_projects_coverage_area ON projects USING GIST (coverage_area);
CREATE INDEX idx_weather_data_location ON weather_data USING GIST (location);

-- Set up spatial reference system (Nigeria is in UTM zone 32N, EPSG:32632)
INSERT INTO spatial_ref_sys (srid, auth_name, auth_srid, proj4text, srtext)
SELECT 
    32632, 'EPSG', 32632,
    '+proj=utm +zone=32 +datum=WGS84 +units=m +no_defs',
    'PROJCS["WGS 84 / UTM zone 32N",GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563,AUTHORITY["EPSG","7030"]],AUTHORITY["EPSG","6326"]],PRIMEM["Greenwich",0,AUTHORITY["EPSG","8901"]],UNIT["degree",0.01745329251994328,AUTHORITY["EPSG","9122"]],AUTHORITY["EPSG","4326"]],PROJECTION["Transverse_Mercator"],PARAMETER["latitude_of_origin",0],PARAMETER["central_meridian",9],PARAMETER["scale_factor",0.9996],PARAMETER["false_easting",500000],PARAMETER["false_northing",0],UNIT["metre",1,AUTHORITY["EPSG","9001"]],AXIS["Easting",EAST],AXIS["Northing",NORTH],AUTHORITY["EPSG","32632"]]'
WHERE NOT EXISTS (
    SELECT 1 FROM spatial_ref_sys WHERE srid = 32632
);

-- Create maintenance function for vacuum and analyze
CREATE OR REPLACE FUNCTION maintenance_db_vacuum()
RETURNS void AS $$
BEGIN
    VACUUM ANALYZE farmers;
    VACUUM ANALYZE farms;
    VACUUM ANALYZE projects;
    VACUUM ANALYZE weather_data;
END;
$$ LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public, pg_temp;
