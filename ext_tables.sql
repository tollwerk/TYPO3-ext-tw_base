# noinspection SqlNoDataSourceInspectionForFile

#
# Modifying pages table
#
CREATE TABLE pages
(
    tx_twbase_title_language VARCHAR(16)         DEFAULT ''  NOT NULL,
    tx_twbase_subnav_hide    TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_twbase_seo_title      VARCHAR(255)        DEFAULT ''  NOT NULL,
);

#
# Modifying tt_content table
#
CREATE TABLE tt_content
(
    tx_twbase_heading_language VARCHAR(16)         DEFAULT ''  NOT NULL,
    tx_twbase_heading_type     TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_twbase_inline           TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_twbase_responsive       TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL,
    tx_twbase_lazyload         TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL,
    tx_twbase_breakpoints      VARCHAR(64)         DEFAULT ''  NOT NULL,
    tx_twbase_skipconverter    VARCHAR(255)        DEFAULT ''  NOT NULL,
    tx_twbase_video_tracks     VARCHAR(255)        DEFAULT ''  NOT NULL,
);

#
# Modifying sys_file_metadata table
#
CREATE TABLE sys_file_metadata
(
    tx_twbase_author        TINYTEXT    DEFAULT '' NOT NULL,
    tx_twbase_author_url    TINYTEXT    DEFAULT '' NOT NULL,
    tx_twbase_source_url    TINYTEXT    DEFAULT '' NOT NULL,
    tx_twbase_creation_year int(11) unsigned,
    tx_twbase_license       VARCHAR(12) DEFAULT '' NOT NULL,
    tx_twbase_license_name  TINYTEXT    DEFAULT '' NOT NULL,
    tx_twbase_license_url   TINYTEXT    DEFAULT '' NOT NULL,
);

#
# Table structure for table 'tx_twbase_domain_model_video_track'
#
CREATE TABLE tx_twbase_domain_model_video_track
(
    uid        int(11)                                 NOT NULL auto_increment,
    pid        int(11)              DEFAULT '0'        NOT NULL,

    kind       varchar(12)          DEFAULT 'captions' NOT NULL,
    file       int(11) unsigned     DEFAULT '0'        NOT NULL,
    language   VARCHAR(16)          DEFAULT ''         NOT NULL,
    transcript TEXT,

    tstamp     int(11) unsigned     DEFAULT '0'        NOT NULL,
    crdate     int(11) unsigned     DEFAULT '0'        NOT NULL,
    cruser_id  int(11) unsigned     DEFAULT '0'        NOT NULL,
    deleted    smallint(5) unsigned DEFAULT '0'        NOT NULL,
    hidden     smallint(5) unsigned DEFAULT '0'        NOT NULL,
    starttime  int(11) unsigned     DEFAULT '0'        NOT NULL,
    endtime    int(11) unsigned     DEFAULT '0'        NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
);
