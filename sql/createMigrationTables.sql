CREATE TABLE IF NOT EXISTS `forumzfd_address_migration_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_address_id` int(11) DEFAULT NULL,
  `source_contact_id` int(11) DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_email_migration_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_email_id` int(11) DEFAULT NULL,
  `source_contact_id` int(11) DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21089 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_entity_tag_migration_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_entity_tag_id` int(11) DEFAULT NULL,
  `source_contact_id` int(11) DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_note_migration_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_note_id` int(11) DEFAULT NULL,
  `source_contact_id` int(11) DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_phone_migration_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_phone_id` int(11) DEFAULT NULL,
  `source_contact_id` int(11) DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15722 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_website_migration_error` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_website_id` int(11) DEFAULT NULL,
  `source_contact_id` int(11) DEFAULT NULL,
  `error_message` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique  Other Activity ID',
  `source_record_id` int(10) unsigned DEFAULT NULL COMMENT 'Artificial FK to original transaction (e.g. contribution) IF it is not an Activity. Table can be figured out through activity_type_id, and further through component registry.',
  `activity_type_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'FK to civicrm_option_value.id, that has to be valid, registered activity type.',
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The subject/purpose/short description of the activity.',
  `activity_date_time` datetime DEFAULT NULL COMMENT 'Date and time this activity is scheduled to occur. Formerly named scheduled_date_time.',
  `duration` int(10) unsigned DEFAULT NULL COMMENT 'Planned or actual duration of activity expressed in minutes. Conglomerate of former duration_hours and duration_minutes.',
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Location of the activity (optional, open text).',
  `phone_id` int(10) unsigned DEFAULT NULL COMMENT 'Phone ID of the number called (optional - used if an existing phone number is selected).',
  `phone_number` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Phone number in case the number does not exist in the civicrm_phone table.',
  `details` longtext COLLATE utf8_unicode_ci COMMENT 'Details about the activity (agenda, notes, etc).',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'ID of the status this activity is currently in. Foreign key to civicrm_option_value.',
  `priority_id` int(10) unsigned DEFAULT NULL COMMENT 'ID of the priority given to this activity. Foreign key to civicrm_option_value.',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Parent meeting ID (if this is a follow-up item). This is not currently implemented',
  `is_test` tinyint(4) DEFAULT '0',
  `medium_id` int(10) unsigned DEFAULT NULL COMMENT 'Activity Medium, Implicit FK to civicrm_option_value where option_group = encounter_medium.',
  `is_auto` tinyint(4) DEFAULT '0',
  `relationship_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Relationship ID',
  `is_current_revision` tinyint(4) DEFAULT '1',
  `original_id` int(10) unsigned DEFAULT NULL COMMENT 'Activity ID of the first activity record in versioning chain.',
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Currently being used to store result id for survey activity, FK to option value.',
  `is_deleted` tinyint(4) DEFAULT '0',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this activity has been triggered.',
  `engagement_level` int(10) unsigned DEFAULT NULL COMMENT 'Assign a specific level of engagement to this activity. Used for tracking constituents in ladder of engagement.',
  `weight` int(11) DEFAULT NULL,
  `is_star` tinyint(4) DEFAULT '0' COMMENT 'Activity marked as favorite.',
  `is_processed` tinyint(4) DEFAULT '0',
  `new_activity_id` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `activity_type_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `UI_source_record_id` (`source_record_id`),
  KEY `UI_activity_type_id` (`activity_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_activity_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Activity contact id',
  `activity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the activity for this record.',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the contact for this record.',
  `record_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Nature of this contact''s role in the activity: 1 assignee, 2 creator, 3 focus or target.',
  `is_processed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UI_activity_contact` (`contact_id`,`activity_id`,`record_type_id`),
  KEY `INDEX_activity_id` (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Address ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this address belong to.',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary address.',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing address.',
  `street_address` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Concatenation of all routable street address components (prefix, street number, street name, suffix, unit\n      number OR P.O. Box). Apps should be able to determine physical location with this data (for mapping, mail\n      delivery, etc.).\n    ',
  `street_number` int(11) DEFAULT NULL COMMENT 'Numeric portion of address number on the street, e.g. For 112A Main St, the street_number = 112.',
  `street_number_suffix` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Non-numeric portion of address number on the street, e.g. For 112A Main St, the street_number_suffix = A\n    ',
  `street_number_predirectional` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Directional prefix, e.g. SE Main St, SE is the prefix.',
  `street_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Actual street name, excluding St, Dr, Rd, Ave, e.g. For 112 Main St, the street_name = Main.',
  `street_type` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'St, Rd, Dr, etc.',
  `street_number_postdirectional` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Directional prefix, e.g. Main St S, S is the suffix.',
  `street_unit` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Secondary unit designator, e.g. Apt 3 or Unit # 14, or Bldg 1200',
  `supplemental_address_1` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supplemental Address Information, Line 1',
  `supplemental_address_2` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supplemental Address Information, Line 2',
  `supplemental_address_3` varchar(96) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supplemental Address Information, Line 3',
  `city` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'City, Town or Village Name.',
  `county_id` int(10) unsigned DEFAULT NULL COMMENT 'Which County does this address belong to.',
  `state_province_id` int(10) unsigned DEFAULT NULL COMMENT 'Which State_Province does this address belong to.',
  `postal_code_suffix` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Store the suffix, like the +4 part in the USPS system.',
  `postal_code` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Store both US (zip5) AND international postal codes. App is responsible for country/region appropriate validation.',
  `usps_adc` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'USPS Bulk mailing code.',
  `country_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Country does this address belong to.',
  `geo_code_1` double DEFAULT NULL COMMENT 'Latitude',
  `geo_code_2` double DEFAULT NULL COMMENT 'Longitude',
  `manual_geo_code` tinyint(4) DEFAULT '0' COMMENT 'Is this a manually entered geo code',
  `timezone` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Timezone expressed as a UTC offset - e.g. United States CST would be written as "UTC-6".',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `master_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Address ID',
  `is_processed` tinyint(4) DEFAULT '0',
  `new_address_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_campaign` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Campaign ID.',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the Campaign.',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title of the Campaign.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Full description of Campaign.',
  `start_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign starts.',
  `end_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign ends.',
  `campaign_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign Type ID.Implicit FK to civicrm_option_value where option_group = campaign_type',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign status ID.Implicit FK to civicrm_option_value where option_group = campaign_status',
  `external_identifier` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unique trusted external ID (generally from a legacy app/datasource). Particularly useful for deduping operations.',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional parent id for this Campaign.',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this Campaign enabled or disabled/cancelled?',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this Campaign.',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign was created.',
  `last_modified_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who recently edited this Campaign.',
  `last_modified_date` datetime DEFAULT NULL COMMENT 'Date and time that Campaign was edited last time.',
  `goal_general` text COLLATE utf8_unicode_ci COMMENT 'General goals for Campaign.',
  `goal_revenue` decimal(20,2) DEFAULT NULL COMMENT 'The target revenue for this campaign.',
  `new_campaign_id` int(10) DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Contact ID',
  `contact_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Type of Contact.',
  `contact_sub_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'May be used to over-ride contact view and edit templates.',
  `do_not_email` tinyint(4) DEFAULT '0',
  `do_not_phone` tinyint(4) DEFAULT '0',
  `do_not_mail` tinyint(4) DEFAULT '0',
  `do_not_sms` tinyint(4) DEFAULT '0',
  `do_not_trade` tinyint(4) DEFAULT '0',
  `is_opt_out` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Has the contact opted out from receiving all bulk email from the organization or site domain?',
  `legal_identifier` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'May be used for SSN, EIN/TIN, Household ID (census) or other applicable unique legal/government ID.\n    ',
  `external_identifier` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Unique trusted external ID (generally from a legacy app/datasource). Particularly useful for deduping operations.',
  `sort_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name used for sorting different contact types',
  `display_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Formatted name representing preferred format for display/print/other output.',
  `nick_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Nickname.',
  `legal_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Legal Name.',
  `image_URL` text COLLATE utf8_unicode_ci COMMENT 'optional URL for preferred image (photo, logo, etc.) to display for this contact.',
  `preferred_communication_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'What is the preferred mode of communication.',
  `preferred_language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Which language is preferred for communication. FK to languages in civicrm_option_value.',
  `preferred_mail_format` varchar(8) COLLATE utf8_unicode_ci DEFAULT 'Both' COMMENT 'What is the preferred mode of sending an email.',
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Key for validating requests related to this contact.',
  `api_key` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'API Key for validating requests related to this contact.',
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'where contact come from, e.g. import, donate module insert...',
  `first_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'First Name.',
  `middle_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Middle Name.',
  `last_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Last Name.',
  `prefix_id` int(10) unsigned DEFAULT NULL COMMENT 'Prefix or Title for name (Ms, Mr...). FK to prefix ID',
  `suffix_id` int(10) unsigned DEFAULT NULL COMMENT 'Suffix for name (Jr, Sr...). FK to suffix ID',
  `formal_title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Formal (academic or similar) title in front of name. (Prof., Dr. etc.)',
  `communication_style_id` int(10) unsigned DEFAULT NULL COMMENT 'Communication style (e.g. formal vs. familiar) to use with this contact. FK to communication styles in civicrm_option_value.',
  `email_greeting_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id, that has to be valid registered Email Greeting.',
  `email_greeting_custom` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Custom Email Greeting.',
  `email_greeting_display` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Cache Email Greeting.',
  `postal_greeting_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id, that has to be valid registered Postal Greeting.',
  `postal_greeting_custom` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Custom Postal greeting.',
  `postal_greeting_display` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Cache Postal greeting.',
  `addressee_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id, that has to be valid registered Addressee.',
  `addressee_custom` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Custom Addressee.',
  `addressee_display` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Cache Addressee.',
  `job_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Job Title',
  `gender_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to gender ID',
  `birth_date` date DEFAULT NULL COMMENT 'Date of birth',
  `is_deceased` tinyint(4) DEFAULT '0',
  `deceased_date` date DEFAULT NULL COMMENT 'Date of deceased',
  `household_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Household Name.',
  `primary_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional FK to Primary Contact for this household.',
  `organization_name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Organization Name.',
  `sic_code` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Standard Industry Classification Code.',
  `user_unique_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'the OpenID (or OpenID-style http://username.domain/) unique identifier for this contact mainly used for logging in to CiviCRM',
  `employer_id` int(10) unsigned DEFAULT NULL COMMENT 'OPTIONAL FK to civicrm_contact record.',
  `is_deleted` tinyint(4) NOT NULL DEFAULT '0',
  `created_date` timestamp NULL DEFAULT NULL COMMENT 'When was the contact was created.',
  `modified_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When was the contact (or closely related entity) was created or modified or deleted.',
  `is_processed` tinyint(4) DEFAULT '0',
  `new_contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_contribution` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Contribution ID',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type for (total_amount - non_deductible_amount).',
  `contribution_page_id` int(10) unsigned DEFAULT NULL COMMENT 'The Contribution Page which triggered this contribution',
  `payment_instrument_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Payment Instrument',
  `receive_date` datetime DEFAULT NULL COMMENT 'Date contribution was received - not necessarily the creation date of the record',
  `non_deductible_amount` decimal(20,2) DEFAULT '0.00' COMMENT 'Portion of total amount which is NOT tax deductible. Equal to total_amount for non-deductible financial types.',
  `total_amount` decimal(20,2) NOT NULL COMMENT 'Total amount of this contribution. Use market value for non-monetary gifts.',
  `fee_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual processor fee if known - may be 0.',
  `net_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual funds transfer amount. total less fees. if processor does not report actual fee during transaction, this is set to total_amount.',
  `trxn_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unique transaction id. may be processor id, bank id + trans id, or account number + check number... depending on payment_method',
  `invoice_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unique invoice id, system generated or passed in',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `cancel_date` datetime DEFAULT NULL COMMENT 'when was gift cancelled',
  `cancel_reason` text COLLATE utf8_unicode_ci,
  `receipt_date` datetime DEFAULT NULL COMMENT 'when (if) receipt was sent. populated automatically for online donations w/ automatic receipting',
  `thankyou_date` datetime DEFAULT NULL COMMENT 'when (if) was donor thanked',
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Origin of this Contribution.',
  `amount_level` text COLLATE utf8_unicode_ci,
  `contribution_recur_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_contribution_recur id. Each contribution made in connection with a recurring contribution carries a foreign key to the recurring contribution record. This assumes we can track these processor initiated events.',
  `is_test` tinyint(4) DEFAULT '0',
  `is_pay_later` tinyint(4) DEFAULT '0',
  `contribution_status_id` int(10) unsigned DEFAULT '1',
  `address_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_address.id. We insert an address record for each contribution when we have associated billing name and address data.',
  `check_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this contribution has been triggered.',
  `creditnote_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'unique credit note id, system generated or passed in',
  `tax_amount` decimal(20,2) DEFAULT NULL COMMENT 'Total tax amount of this contribution.',
  `revenue_recognition_date` datetime DEFAULT NULL COMMENT 'Stores the date when revenue should be recognized.',
  `new_contribution_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_custom_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Custom Field ID',
  `custom_group_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_custom_group.',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Variable name/programmatic handle for this group.',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Text for form field label (also friendly name for administering this custom property).',
  `data_type` varchar(16) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Controls location of data storage in extended_data table.',
  `html_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'HTML types plus several built-in extended types.',
  `default_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Use form_options.is_default for field_types which use options.',
  `is_required` tinyint(4) DEFAULT NULL COMMENT 'Is a value required for this property.',
  `is_searchable` tinyint(4) DEFAULT NULL COMMENT 'Is this property searchable.',
  `is_search_range` tinyint(4) DEFAULT '0' COMMENT 'Is this property range searchable.',
  `weight` int(11) NOT NULL DEFAULT '1' COMMENT 'Controls field display order within an extended property group.',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before this field.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after this field.',
  `mask` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional format instructions for specific field types, like date types.',
  `attributes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Store collection of type-appropriate attributes, e.g. textarea  needs rows/cols attributes',
  `javascript` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional scripting attributes for field.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `is_view` tinyint(4) DEFAULT NULL COMMENT 'Is this property set by PHP Code? A code field is viewable but not editable',
  `options_per_line` int(10) unsigned DEFAULT NULL COMMENT 'number of options per line for checkbox and radio',
  `text_length` int(10) unsigned DEFAULT NULL COMMENT 'field length if alphanumeric',
  `start_date_years` int(11) DEFAULT NULL COMMENT 'Date may be up to start_date_years years prior to the current date.',
  `end_date_years` int(11) DEFAULT NULL COMMENT 'Date may be up to end_date_years years after the current date.',
  `date_format` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'date format for custom date',
  `time_format` int(10) unsigned DEFAULT NULL COMMENT 'time format for custom date',
  `note_columns` int(10) unsigned DEFAULT NULL COMMENT ' Number of columns in Note Field ',
  `note_rows` int(10) unsigned DEFAULT NULL COMMENT ' Number of rows in Note Field ',
  `column_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the column that holds the values for this field.',
  `option_group_id` int(10) unsigned DEFAULT NULL COMMENT 'For elements with options, the option group id that is used',
  `filter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Stores Contact Get API params contact reference custom fields. May be used for other filters in the future.',
  `in_selector` tinyint(4) DEFAULT '0' COMMENT 'Should the multi-record custom field values be displayed in tab table listing',
  `new_custom_field_id` int(10) unsigned DEFAULT NULL,
  `target_custom_group_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_custom_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Custom Group ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Variable name/programmatic handle for this group.',
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Friendly Name.',
  `extends` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'Contact' COMMENT 'Type of object this group extends (can add other options later e.g. contact_address, etc.).',
  `extends_entity_column_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.id (for option group custom_data_type.)',
  `extends_entity_column_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'linking custom group for dynamic object',
  `style` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Visual relationship between this form and its parent.',
  `collapse_display` int(10) unsigned DEFAULT '0' COMMENT 'Will this group be in collapsed or expanded mode on initial display ?',
  `help_pre` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display before fields in form.',
  `help_post` text COLLATE utf8_unicode_ci COMMENT 'Description and/or help text to display after fields in form.',
  `weight` int(11) NOT NULL DEFAULT '1' COMMENT 'Controls display order when multiple extended property groups are setup for the same class.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `table_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of the table that holds the values for this group.',
  `is_multiple` tinyint(4) DEFAULT NULL COMMENT 'Does this group hold multiple values?',
  `min_multiple` int(10) unsigned DEFAULT NULL COMMENT 'minimum number of multiple records (typically 0?)',
  `max_multiple` int(10) unsigned DEFAULT NULL COMMENT 'maximum number of multiple records, if 0 - no max',
  `collapse_adv_display` int(10) unsigned DEFAULT '0' COMMENT 'Will this group be in collapsed or expanded mode on advanced search display ?',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this custom group',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time this custom group was created.',
  `is_reserved` tinyint(4) DEFAULT '0' COMMENT 'Is this a reserved Custom Group?',
  `new_custom_group_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Email ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this email belong to.',
  `email` varchar(254) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Email address',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary?',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing?',
  `on_hold` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Is this address on bounce hold?',
  `is_bulkmail` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Is this address for bulk mail ?',
  `hold_date` datetime DEFAULT NULL COMMENT 'When the address went on bounce hold',
  `reset_date` datetime DEFAULT NULL COMMENT 'When the address bounce status was last reset',
  `signature_text` text COLLATE utf8_unicode_ci COMMENT 'Text formatted signature for the email.',
  `signature_html` text COLLATE utf8_unicode_ci COMMENT 'HTML formatted signature for the email.',
  `new_email_id` int(11) DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_entity_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'physical tablename for entity being joined to file, e.g. civicrm_contact',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'FK to entity table specified in entity_table column.',
  `tag_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_tag',
  `new_entity_tag_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Event',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Event Title (e.g. Fall Fundraiser Dinner)',
  `summary` text COLLATE utf8_unicode_ci COMMENT 'Brief summary of event. Text and html allowed. Displayed on Event Registration form and can be used on other CMS pages which need an event summary.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Full description of event. Text and html allowed. Displayed on built-in Event Information screens.',
  `event_type_id` int(10) unsigned DEFAULT '0' COMMENT 'Event Type ID.Implicit FK to civicrm_option_value where option_group = event_type.',
  `participant_listing_id` int(10) unsigned DEFAULT '0' COMMENT 'Should we expose the participant list? Implicit FK to civicrm_option_value where option_group = participant_listing.',
  `is_public` tinyint(4) DEFAULT '1' COMMENT 'Public events will be included in the iCal feeds. Access to private event information may be limited using ACLs.',
  `start_date` datetime DEFAULT NULL COMMENT 'Date and time that event starts.',
  `end_date` datetime DEFAULT NULL COMMENT 'Date and time that event ends. May be NULL if no defined end date/time',
  `is_online_registration` tinyint(4) DEFAULT '0' COMMENT 'If true, include registration link on Event Info page.',
  `registration_link_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Text for link to Event Registration form which is displayed on Event Information screen when is_online_registration is true.',
  `registration_start_date` datetime DEFAULT NULL COMMENT 'Date and time that online registration starts.',
  `registration_end_date` datetime DEFAULT NULL COMMENT 'Date and time that online registration ends.',
  `max_participants` int(10) unsigned DEFAULT NULL COMMENT 'Maximum number of registered participants to allow. After max is reached, a custom Event Full message is displayed. If NULL, allow unlimited number of participants.',
  `event_full_text` text COLLATE utf8_unicode_ci COMMENT 'Message to display on Event Information page and INSTEAD OF Event Registration form if maximum participants are signed up. Can include email address/info about getting on a waiting list, etc. Text and html allowed.',
  `is_monetary` tinyint(4) DEFAULT '0' COMMENT 'If true, one or more fee amounts must be set and a Payment Processor must be configured for Online Event Registration.',
  `financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Financial type assigned to paid event registrations for this event. Required if is_monetary is true.',
  `payment_processor` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Payment Processors configured for this Event (if is_monetary is true)',
  `is_map` tinyint(4) DEFAULT '0' COMMENT 'Include a map block on the Event Information page when geocode info is available and a mapping provider has been specified?',
  `is_active` tinyint(4) DEFAULT '0' COMMENT 'Is this Event enabled or disabled/cancelled?',
  `fee_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_show_location` tinyint(4) DEFAULT '1' COMMENT 'If true, show event location.',
  `loc_block_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Location Block ID',
  `default_role_id` int(10) unsigned DEFAULT '1' COMMENT 'Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.',
  `intro_text` text COLLATE utf8_unicode_ci COMMENT 'Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.',
  `footer_text` text COLLATE utf8_unicode_ci COMMENT 'Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.',
  `confirm_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for Confirmation page.',
  `confirm_text` text COLLATE utf8_unicode_ci COMMENT 'Introductory message for Event Registration page. Text and html allowed. Displayed at the top of Event Registration form.',
  `confirm_footer_text` text COLLATE utf8_unicode_ci COMMENT 'Footer message for Event Registration page. Text and html allowed. Displayed at the bottom of Event Registration form.',
  `is_email_confirm` tinyint(4) DEFAULT '0' COMMENT 'If true, confirmation is automatically emailed to contact on successful registration.',
  `confirm_email_text` text COLLATE utf8_unicode_ci COMMENT 'text to include above standard event info on confirmation email. emails are text-only, so do not allow html for now',
  `confirm_from_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FROM email name used for confirmation emails.',
  `confirm_from_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FROM email address used for confirmation emails.',
  `cc_confirm` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma-separated list of email addresses to cc each time a confirmation is sent',
  `bcc_confirm` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma-separated list of email addresses to bcc each time a confirmation is sent',
  `default_fee_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.',
  `default_discount_fee_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_option_value.',
  `thankyou_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Title for ThankYou page.',
  `thankyou_text` text COLLATE utf8_unicode_ci COMMENT 'ThankYou Text.',
  `thankyou_footer_text` text COLLATE utf8_unicode_ci COMMENT 'Footer message.',
  `is_pay_later` tinyint(4) DEFAULT '0' COMMENT 'if true - allows the user to send payment directly to the org later',
  `pay_later_text` text COLLATE utf8_unicode_ci COMMENT 'The text displayed to the user in the main form',
  `pay_later_receipt` text COLLATE utf8_unicode_ci COMMENT 'The receipt sent to the user instead of the normal receipt text',
  `is_partial_payment` tinyint(4) DEFAULT '0' COMMENT 'is partial payment enabled for this event',
  `initial_amount_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Initial amount label for partial payment',
  `initial_amount_help_text` text COLLATE utf8_unicode_ci COMMENT 'Initial amount help text for partial payment',
  `min_initial_amount` decimal(20,2) DEFAULT NULL COMMENT 'Minimum initial amount for partial payment',
  `is_multiple_registrations` tinyint(4) DEFAULT '0' COMMENT 'if true - allows the user to register multiple participants for event',
  `max_additional_participants` int(10) unsigned DEFAULT '0' COMMENT 'Maximum number of additional participants that can be registered on a single booking',
  `allow_same_participant_emails` tinyint(4) DEFAULT '0' COMMENT 'if true - allows the user to register multiple registrations from same email address.',
  `has_waitlist` tinyint(4) DEFAULT NULL COMMENT 'Whether the event has waitlist support.',
  `requires_approval` tinyint(4) DEFAULT NULL COMMENT 'Whether participants require approval before they can finish registering.',
  `expiration_time` int(10) unsigned DEFAULT NULL COMMENT 'Expire pending but unconfirmed registrations after this many hours.',
  `allow_selfcancelxfer` tinyint(4) DEFAULT '0' COMMENT 'Allow self service cancellation or transfer for event?',
  `selfcancelxfer_time` int(10) unsigned DEFAULT '0' COMMENT 'Number of hours prior to event start date to allow self-service cancellation or transfer.',
  `waitlist_text` text COLLATE utf8_unicode_ci COMMENT 'Text to display when the event is full, but participants can signup for a waitlist.',
  `approval_req_text` text COLLATE utf8_unicode_ci COMMENT 'Text to display when the approval is required to complete registration for an event.',
  `is_template` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'whether the event has template',
  `template_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Event Template Title',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_contact, who created this event',
  `created_date` datetime DEFAULT NULL COMMENT 'Date and time that event was created.',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value from config setting or input via user.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this event has been created.',
  `is_share` tinyint(4) DEFAULT '1' COMMENT 'Can people share the event through social media?',
  `is_confirm_enabled` tinyint(4) DEFAULT '1' COMMENT 'If false, the event booking confirmation screen gets skipped',
  `parent_event_id` int(10) unsigned DEFAULT NULL COMMENT 'Implicit FK to civicrm_event: parent event',
  `slot_label_id` int(10) unsigned DEFAULT NULL COMMENT 'Subevent slot label. Implicit FK to civicrm_option_value where option_group = conference_slot.',
  `dedupe_rule_group_id` int(10) unsigned DEFAULT NULL COMMENT 'Rule to use when matching registrations for this event',
  `is_billing_required` tinyint(4) DEFAULT '0' COMMENT 'if true than billing block is required this event',
  `new_event_id` int(10) DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Group ID',
  `name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Internal name of Group.',
  `title` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Name of Group.',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Optional verbose description of the group.',
  `source` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Module or process which created this group.',
  `saved_search_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to saved search table.',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this entry active?',
  `visibility` varchar(24) COLLATE utf8_unicode_ci DEFAULT 'User and User Admin Only' COMMENT 'In what context(s) is this field visible.',
  `where_clause` text COLLATE utf8_unicode_ci COMMENT 'the sql where clause if a saved search acl',
  `select_tables` text COLLATE utf8_unicode_ci COMMENT 'the tables to be included in a select data',
  `where_tables` text COLLATE utf8_unicode_ci COMMENT 'the tables to be included in the count statement',
  `group_type` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'FK to group type',
  `cache_date` timestamp NULL DEFAULT NULL COMMENT 'Date when we created the cache for a smart group',
  `refresh_date` timestamp NULL DEFAULT NULL COMMENT 'Date and time when we need to refresh the cache next.',
  `parents` text COLLATE utf8_unicode_ci COMMENT 'IDs of the parent(s)',
  `children` text COLLATE utf8_unicode_ci COMMENT 'IDs of the child(ren)',
  `is_hidden` tinyint(4) DEFAULT '0' COMMENT 'Is this group hidden?',
  `is_reserved` tinyint(4) DEFAULT '0',
  `created_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact table.',
  `modified_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to contact table.',
  `new_group_id` int(10) DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_group_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary key',
  `group_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_group',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_contact',
  `status` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'status of contact relative to membership in group',
  `location_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional location to associate with this membership',
  `email_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional email to associate with this membership',
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_membership` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Membership Id',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `membership_type_id` int(10) unsigned NOT NULL COMMENT 'FK to Membership Type',
  `join_date` date DEFAULT NULL COMMENT 'Beginning of initial membership period (member since...).',
  `start_date` date DEFAULT NULL COMMENT 'Beginning of current uninterrupted membership period.',
  `end_date` date DEFAULT NULL COMMENT 'Current membership period expire date.',
  `source` varchar(128) DEFAULT NULL,
  `status_id` int(10) unsigned NOT NULL COMMENT 'FK to Membership Status',
  `is_override` tinyint(4) DEFAULT NULL COMMENT 'Admin users may set a manual status which overrides the calculated status. When this flag is true, automated status update scripts should NOT modify status for the record.',
  `owner_membership_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional FK to Parent Membership.',
  `max_related` int(10) unsigned DEFAULT NULL COMMENT 'Maximum number of related memberships (membership_type override).',
  `is_test` tinyint(4) DEFAULT '0',
  `is_pay_later` tinyint(4) DEFAULT '0',
  `contribution_recur_id` int(10) unsigned DEFAULT NULL COMMENT 'Conditional foreign key to civicrm_contribution_recur.id.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this membership is attached.',
  `new_membership_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_note` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Note ID',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of table where item being referenced is stored.',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Foreign key to the referenced item.',
  `note` text COLLATE utf8_unicode_ci COMMENT 'Note and/or Comment.',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID creator',
  `modified_date` date DEFAULT NULL COMMENT 'When was this note last modified/edited',
  `new_note_id` int(10) DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'subject of note description',
  `privacy` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Foreign Key to Note Privacy Level (which is an option value pair and hence an implicit FK)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_participant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Participant Id',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID',
  `event_id` int(10) unsigned NOT NULL COMMENT 'FK to Event ID',
  `status_id` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Participant status ID. FK to civicrm_participant_status_type. Default of 1 should map to status = Registered.',
  `role_id` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Participant role ID. Implicit FK to civicrm_option_value where option_group = participant_role.',
  `register_date` datetime DEFAULT NULL COMMENT 'When did contact register for event?',
  `source` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Source of this event registration.',
  `fee_level` text COLLATE utf8_unicode_ci COMMENT 'Populate with the label (text) associated with a fee level for paid events with multiple levels. Note that\n      we store the label value and not the key\n    ',
  `is_test` tinyint(4) DEFAULT '0',
  `is_pay_later` tinyint(4) DEFAULT '0',
  `fee_amount` decimal(20,2) DEFAULT NULL COMMENT 'actual processor fee if known - may be 0.',
  `registered_by_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Participant ID',
  `discount_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Discount ID',
  `fee_currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '3 character string, value derived from config setting.',
  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'The campaign for which this participant has been registered.',
  `discount_amount` int(10) unsigned DEFAULT NULL COMMENT 'Discount Amount',
  `cart_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_event_carts',
  `must_wait` int(11) DEFAULT NULL COMMENT 'On Waiting List',
  `transferred_to_contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `new_participant_id` int(10) DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_phone` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Phone ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `location_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Location does this phone belong to.',
  `is_primary` tinyint(4) DEFAULT '0' COMMENT 'Is this the primary phone for this contact and location.',
  `is_billing` tinyint(4) DEFAULT '0' COMMENT 'Is this the billing?',
  `mobile_provider_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Mobile Provider does this phone belong to.',
  `phone` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Complete phone number.',
  `phone_ext` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional extension for a phone number.',
  `phone_numeric` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Phone number stripped of all whitespace, letters, and punctuation.',
  `phone_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which type of phone does this number belongs.',
  `new_phone_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_relationship` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Relationship ID',
  `contact_id_a` int(10) unsigned NOT NULL COMMENT 'id of the first contact',
  `contact_id_b` int(10) unsigned NOT NULL COMMENT 'id of the second contact',
  `relationship_type_id` int(10) unsigned NOT NULL COMMENT 'id of the relationship',
  `start_date` date DEFAULT NULL COMMENT 'date when the relationship started',
  `end_date` date DEFAULT NULL COMMENT 'date when the relationship ended',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'is the relationship active ?',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Optional verbose description for the relationship.',
  `is_permission_a_b` tinyint(4) DEFAULT '0' COMMENT 'is contact a has permission to view / edit contact and\n      related data for contact b ?\n    ',
  `is_permission_b_a` tinyint(4) DEFAULT '0' COMMENT 'is contact b has permission to view / edit contact and\n      related data for contact a ?\n    ',
  `case_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_case',
  `new_relationship_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  `new_relationship_type_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_subscription_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Internal Id',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'Contact Id',
  `group_id` int(10) unsigned DEFAULT NULL COMMENT 'Group Id',
  `date` datetime NOT NULL COMMENT 'Date of the (un)subscription',
  `method` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'How the (un)subscription was triggered',
  `status` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The state of the contact within the group',
  `tracking` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'IP address or other tracking info',
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_abnehmer_organisationen_17` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `kundengruppe_115` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_additional_preferences_2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `kontaktwunsch_12` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_akademie_12` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `kursteilnahme_84` varchar(255) DEFAULT '',
  `aufgaben_im_akademie_team_437` text,
  `gewichtung_anzeige_teamseite_438` int(11) DEFAULT NULL,
  `funktion_en__454` varchar(255) DEFAULT NULL,
  `aufgaben_im_akademie_team_en__455` text,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_communication_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `best_time_to_contact` varchar(255) DEFAULT NULL,
  `communication_status` varchar(255) DEFAULT NULL,
  `reason_for_do_not_mail` varchar(255) DEFAULT NULL,
  `reason_for_do_not_phone` varchar(255) DEFAULT NULL,
  `reason_for_do_not_email` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_demographics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `ethnicity` varchar(255) DEFAULT NULL,
  `secondary_language` varchar(255) DEFAULT NULL,
  `kids` tinyint(4) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_felder_f_r_bestellungsaktivit_ten_56` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `produkt_das_bestellt_wurde_500` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_felder_f_r_case__kursbewerbung__21` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `bewerbung_f_r_welchen_kurs__138` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_friedenslauf_13` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `friedenslauf_88` varchar(255) DEFAULT NULL,
  `generell_kein_interesse_89` tinyint(4) DEFAULT NULL,
  `anzahl_teilnehmende_90` double DEFAULT NULL,
  `vorraussichtliche_teilnahme_91` datetime DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_fundraising_10` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `versand_quittungen_59` varchar(255) DEFAULT NULL,
  `studienreise_interesse_70` varchar(255) DEFAULT NULL,
  `zahlungsweise_71` varchar(255) DEFAULT NULL,
  `kampagne402010_72` varchar(255) DEFAULT NULL,
  `vermaechtnisdarlehen_73` varchar(255) DEFAULT NULL,
  `status_kontakt_74` varchar(255) DEFAULT NULL,
  `studienreise_teilnahme_75` varchar(255) DEFAULT NULL,
  `telefonaktion_2006_86` varchar(255) DEFAULT NULL,
  `telefonaktion_2007_87` varchar(255) DEFAULT NULL,
  `telefonaktion_2008_97` varchar(255) DEFAULT NULL,
  `endsumme_erh_hung_f_rderbeitrag__98` double DEFAULT NULL,
  `telefonaktion_2010_111` varchar(255) DEFAULT NULL,
  `endsumme_erh_hung_f_rderbeitrag__112` double DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_further_information_20` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `morning_panels_28_april_128` tinyint(4) DEFAULT NULL,
  `afternoon_panels_28_april_129` tinyint(4) DEFAULT NULL,
  `evening_panel_discussion_28_apri_130` tinyint(4) DEFAULT NULL,
  `morning_panels_29_april_131` tinyint(4) DEFAULT NULL,
  `afternoon_panels_29_april_132` tinyint(4) DEFAULT NULL,
  `subscribe_to_academy_newsletter_133` tinyint(4) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_grant_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `average_amount` decimal(20,2) DEFAULT NULL,
  `funding_areas` varchar(255) DEFAULT NULL,
  `requirements_notes` text,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_informationen_1` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `abo_fbf_4` varchar(255) DEFAULT NULL,
  `fbf_dauerabo_5` tinyint(4) DEFAULT NULL,
  `fbf_exemplare_6` int(11) DEFAULT NULL,
  `infos_nur_auf_englisch_8` tinyint(4) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_kampagnen_f_r_unterschriftenliste_52` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `unterschriftenliste_470` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_lobby_22` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `wahlkreis_141` varchar(255) DEFAULT NULL,
  `plz_bereich_142` varchar(255) DEFAULT NULL,
  `ausschussmitgliedschaften_143` varchar(255) DEFAULT NULL,
  `weitere_funktionen_144` varchar(2550) DEFAULT NULL,
  `parteizugeh_rigkeit_145` varchar(255) DEFAULT NULL,
  `pol_funktion_148` varchar(255) DEFAULT NULL,
  `zus_tzliche_informationen_149` text,
  `bundestagsfraktion_227` varchar(255) DEFAULT NULL,
  `wahlperiode_234` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_lobby_notizen_39` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `gespr_chsnotiz_lobby_219` int(10) unsigned DEFAULT NULL,
  `lobby_gespr_chsdatum_223` datetime DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_notfallkontakte_51` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `notfallkontakt_priorit_t_1_468` int(10) unsigned DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_organisationsdetails_5` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `organisationstyp_28` varchar(255) DEFAULT NULL,
  `sponsoren_30` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_organizational_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `rating` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_presse_6` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `medium_31` varchar(255) DEFAULT NULL,
  `fachpresse_32` tinyint(4) DEFAULT NULL,
  `in_presseverteiler_33` tinyint(4) DEFAULT NULL,
  `themen_35` varchar(255) DEFAULT NULL,
  `vip_kontakt_152` tinyint(4) DEFAULT NULL,
  `dateianhang_153` int(10) unsigned DEFAULT NULL,
  `position_154` varchar(255) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_r_ckmeldecode_bevor_es_zu_sp_t_ist__50` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `r_ckmeldecode_461` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_trainerin_19` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `themengebiete_120` varchar(510) DEFAULT NULL,
  `funktion_121` varchar(255) DEFAULT NULL,
  `t_tig_in_122` varchar(255) DEFAULT NULL,
  `sprachen_123` varchar(255) DEFAULT NULL,
  `umsatzsteuerpflichtig_126` tinyint(4) DEFAULT NULL,
  `regionen_135` varchar(255) DEFAULT NULL,
  `bewerbung_140` varchar(255) DEFAULT NULL,
  `keine_weiter_zusammenarbeit_147` varchar(255) DEFAULT NULL,
  `kommentare_150` text,
  `lebenslauf_151` int(10) unsigned DEFAULT NULL,
  `hat_bereits_f_r_afk_gearbeitet_228` tinyint(4) DEFAULT NULL,
  `projekttitel_229` varchar(255) DEFAULT NULL,
  `anzeige_in_traineruebersicht_447` tinyint(4) DEFAULT NULL,
  `trainer_hintergrund_448` text,
  `fachgebiete_452` text,
  `fachgebiete_en__464` text,
  `hintergrund_en_neu__466` text,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_weitere_personenbezogene_daten_57` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `nationalit_t_501` int(10) unsigned DEFAULT '1082',
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_werbecodes_55` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `werbe_499` int(11) DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_zahlungsdetails_9` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `sollbetrag_46` decimal(20,2) DEFAULT NULL,
  `einzug_47` varchar(255) DEFAULT NULL,
  `ueberweisung_49` varchar(255) DEFAULT NULL,
  `austrittsdatum_233` datetime DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_zusatzinfos_online_seminare_58` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `what_is_your_motivation_to_take__502` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `how_would_you_like_to_be_called__503` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_zusatzinfos_seminaranmeldung_45` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `erfahrung_330` text COLLATE utf8_unicode_ci,
  `erwartungen_331` text COLLATE utf8_unicode_ci,
  `von_organisation_entsendet_332` tinyint(4) DEFAULT NULL,
  `entsendeorganisation_konsortium_333` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entsendeorganisation_334` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `einsatzland_335` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position_projekt_336` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lokaler_internationaler_ma_337` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unterkunft_benoetigt_338` tinyint(4) DEFAULT NULL,
  `erste_mahlzeit_anreisetag_339` tinyint(4) DEFAULT NULL,
  `letzte_mahlzeit_abreisetag_340` tinyint(4) DEFAULT NULL,
  `ankunftszeit_alt_341` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ankunftszeit_342` datetime DEFAULT NULL,
  `besondere_wuensche_343` text COLLATE utf8_unicode_ci,
  `agb_akzeptiert_344` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sonstige_bemerkungen_345` text COLLATE utf8_unicode_ci,
  `woher_kennen_sie_die_akademie_346` text COLLATE utf8_unicode_ci,
  `zahlungeingang_347` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `abreisedatum_453` datetime DEFAULT NULL,
  `bezuschussung_beantragt_467` tinyint(4) DEFAULT '0',
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_zusatzinfos_veranstaltungen_44` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `veranstaltungssprache_303` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dozent_in_311` text COLLATE utf8_unicode_ci,
  `org_ap_312` text COLLATE utf8_unicode_ci,
  `inhalt_ap_313` text COLLATE utf8_unicode_ci,
  `bewerbungsschluss_325` datetime DEFAULT NULL,
  `tagungsort_326` text COLLATE utf8_unicode_ci,
  `preis_449` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fr_hbucherpreis_450` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zusatzkosten_bernachtung_451` text COLLATE utf8_unicode_ci,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_zusatzinfos_weiterbildung_berufsbegleitend_46` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `aktueller_arbeitgeber_348` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aufgabenbereich_349` text COLLATE utf8_unicode_ci,
  `beruflicher_werdegang_erfahrunge_350` text COLLATE utf8_unicode_ci,
  `bisherige_ausbildung_351` text COLLATE utf8_unicode_ci,
  `andere_qualifikationen_352` text COLLATE utf8_unicode_ci,
  `erfahrung_email_353` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_textverarbeitung_354` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_suchmaschinen_355` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_audiokonferenz_356` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_foren_357` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_textchat_358` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_blogs_359` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_wikis_360` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_lernplattformen_361` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrung_virtuelles_klassenzimm_362` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `eigener_laptop_363` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `englischkenntnisse_364` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `motivation_365` text COLLATE utf8_unicode_ci,
  `berufliche_zukunft_366` text COLLATE utf8_unicode_ci,
  `sonstige_bemerkungen_367` text COLLATE utf8_unicode_ci,
  `wie_haben_sie_von_unseren_angebo_469` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_value_zusatzinfos_weiterbildung_vollzeit_47` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `hoechster_abschluss_368` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ausbildung_1_369` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_ausbildung_1_370` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `einrichtung_abschluss_1_371` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ausbildung_2_372` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_ausbildung_2_373` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `einrichtung_abschluss_2_374` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ausbildung_3_375` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_ausbildung_3_376` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `einrichtung_abschluss_3_377` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weitere_erfahrungen_378` text COLLATE utf8_unicode_ci,
  `erlernter_beruf_379` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aktueller_arbeitgeber_380` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `aufgabenbereich_381` text COLLATE utf8_unicode_ci,
  `berufserfahrung_1_382` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_berufserfahrung_1_383` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `beschreibung_berufserfahrung_1_384` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `berufserfahrung_2_385` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_berufserfahrung_2_386` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `beschreibung_berufserfahrung_2_387` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `berufserfahrung_3_388` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_berufserfahrung_3_389` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `beschreibung_berufserfahrung_3_390` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ehrenamt_1_391` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_ehrenamt_1_392` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `beschreibung_ehrenamt_1_393` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ehrenamt_2_394` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_ehrenamt_2_395` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `beschreibung_ehrenamt_2_396` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ehrenamt_3_397` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zeit_ort_ehrenamt_3_398` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `beschreibung_ehrenamt_3_399` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erfahrungen_inwiefern_relevant_400` text COLLATE utf8_unicode_ci,
  `dauer_arbeit_krisengebiete_401` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `muttersprache_402` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zweite_muttersprache_403` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erste_fremdsprache_404` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erste_fremdsprache_sprachlevel_s_405` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `erste_fremdsprache_sprachlevel_m_406` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zweite_fremdsprache_407` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zweite_fremdsprache_sprachlevel__408` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zweite_fremdsprache_sprachlevel__409` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dritte_fremdsprache_410` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dritte_fremdsprache_sprachlevel__411` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dritte_fremdsprache_sprachlevel__412` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vierte_fremdsprache_413` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vierte_fremdsprache_sprachlevel__414` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vierte_fremdsprache_sprachlevel__415` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bewerbung_nur_grundlagenkurs_416` tinyint(4) DEFAULT NULL,
  `motivation_417` text COLLATE utf8_unicode_ci,
  `andere_auslandsaufenthalte_418` text COLLATE utf8_unicode_ci,
  `im_auftrag_von_organisation_419` tinyint(4) DEFAULT NULL,
  `welche_organisation_420` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `andere_erfahrungen_421` text COLLATE utf8_unicode_ci,
  `teilnahme_orientierungstag_422` tinyint(4) DEFAULT NULL,
  `welcher_orientierungstag_423` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kursfinanzierung_424` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `berufsperspektiven_425` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sonstige_bemerkungen_426` text COLLATE utf8_unicode_ci,
  `wie_von_angebot_erfahren_427` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `familienstand_428` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zahl_kinder_429` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `forumzfd_website` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Website ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact ID',
  `url` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Website',
  `website_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Which Website type does this website belong to.',
  `new_website_id` int(10) unsigned DEFAULT NULL,
  `is_processed` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



























