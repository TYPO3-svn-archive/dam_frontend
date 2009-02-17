CREATE TABLE pages_tx_damtree_dam_cats_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE pages (
    tx_damtree_dam_cats int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_dam_cat_readaccess_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_dam_cat_downloadaccess_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_dam_cat_uploadaccess_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);

CREATE TABLE tx_dam_cat (
    tx_damtree_fe_groups_readaccess int(11) DEFAULT '0' NOT NULL,
    tx_damtree_fe_groups_downloadaccess int(11) DEFAULT '0' NOT NULL
    tx_damtree_fe_groups_uploadaccess int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE tx_damfrontend_filterStates (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    fe_user int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    title tinytext NOT NULL,
    description text NOT NULL,
    filter_from int(11) DEFAULT '0' NOT NULL,
    filter_to int(11) DEFAULT '0' NOT NULL,
    searchword tinytext NOT NULL,
    filetypes mediumblob NOT NULL,
    categories mediumblob NOT NULL,
    
    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# fügt das Entscheidungsfeld ein, ob eine uid verwendet werden soll
#
CREATE TABLE tx_dam (
    tx_damfrontend_use_request_form int(11) DEFAULT '0' NOT NULL,
    tx_damfrontend_feuser_upload int(11),
	tx_damfrontend_version int(11) 
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
    tx_damdownloadlist_records blob NOT NULL
);
