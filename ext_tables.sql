# noinspection SqlNoDataSourceInspectionForFile

#
# Modifying tt_content table
#
CREATE TABLE tt_content (
    tx_twbase_heading_type TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_twbase_responsive TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL,
    tx_twbase_lazyload TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL
);
