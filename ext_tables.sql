# noinspection SqlNoDataSourceInspectionForFile

#
# Modifying pages table
#
CREATE TABLE pages (
    tx_twbase_title_language VARCHAR(16) NOT NULL,
);

#
# Modifying tt_content table
#
CREATE TABLE tt_content (
    tx_twbase_heading_type TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_twbase_inline TINYINT(1) UNSIGNED DEFAULT '0' NOT NULL,
    tx_twbase_responsive TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL,
    tx_twbase_lazyload TINYINT(1) UNSIGNED DEFAULT '1' NOT NULL,
    tx_twbase_breakpoints VARCHAR(64) NOT NULL,
    tx_twbase_skipconverter VARCHAR(255) NOT NULL
);
