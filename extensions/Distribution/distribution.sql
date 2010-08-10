-- MySQL version of the database schema for the Distribution extension.

-- Packages can contain multiple units, either pointing to the units themselves,
-- which will get the latest version, or a specific version.
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/distribution_packages (
  package_id               INT(8) unsigned   NOT NULL auto_increment PRIMARY KEY
  --need to figure out how to best link units/versions here,
  --this (aka package functionality) is not needed in the initial version though.
) /*$wgDBTableOptions*/;

-- Units are individual extensions, or mw core. They contain data non-specific to
-- the different versions, as this is stored in distribution_unit_versions.
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/distribution_units (
  unit_id                 INT(8) unsigned   NOT NULL auto_increment PRIMARY KEY,
  unit_name               VARCHAR(255)      NOT NULL,
  -- Latest stable release id.
  unit_current            INT(8) unsigned   NOT NULL,
  -- Select info of the latest release to avoid extra lookups.
  current_version_nr      VARCHAR(20)       NOT NULL,
  current_desc            BLOB              NOT NULL,
  current_authors         BLOB              NOT NULL,
  current_url             VARCHAR(255)      NULL
  -- early adoptor stuff can be here
) /*$wgDBTableOptions*/;

CREATE UNIQUE INDEX unit_name ON /*$wgDBprefix*/distribution_units (unit_name);

-- Specific versions of units.
CREATE TABLE IF NOT EXISTS /*$wgDBprefix*/distribution_unit_versions (
  version_id               INT(8) unsigned   NOT NULL auto_increment PRIMARY KEY,
  --might want to have this as int to compare?
  version_nr               VARCHAR(20)       NOT NULL,
  
  unit_id                  INT(8) unsigned   NOT NULL,
  FOREIGN KEY (unit_id) REFERENCES /*$wgDBprefix*/distribution_units(unit_id),

  --enum with release status (alpha, beta, rc, supported, deprecated, ...)
  version_status           TINYINT unsigned  NOT NULL,
  
  version_desc             BLOB              NOT NULL,
  --work with an extra table to be able to filter on authors?
  version_authors          BLOB              NOT NULL,
  version_url              VARCHAR(255)      NULL
  --... more stuff be here, but let's not bother for an initial version.
  
  --dependency info
  --compatibility info
) /*$wgDBTableOptions*/;