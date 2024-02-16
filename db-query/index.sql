ALTER TABLE `walk_through_screens` ADD `type` ENUM('alysei','marketplace') NOT NULL DEFAULT 'alysei' AFTER `order`;

php artisan migrate --path=Modules/User/Database/Migrations/2022_12_24_163834_create_table_walk_through_points.php

php artisan migrate --path=Modules/User/Database/Migrations/2022_12_26_212132_create_site_languages_table.php

UPDATE `user_fields` SET `title` = 'Label' WHERE `user_fields`.`user_field_id` = 5;

UPDATE `user_fields` SET `hint` = 'Choose the label to use', `deleted_at` = NULL, `created_at` = NULL, `updated_at` = NULL WHERE `user_fields`.`user_field_id` = 5;
update `notifications` set notification_type=6 WHERE `title_en` LIKE '%liked your comment%';

ALTER TABLE `marketplace_stores` ADD `first_product_id` INT(20) NULL AFTER `status`;

php artisan migrate --path=Modules/Miscellaneous/Database/Migrations/2022_12_28_111103_create_app_versions_table.php

php artisan migrate --path=Modules/Miscellaneous/Database/Migrations/2022_12_28_114351_create_cron_jobs_table.php
php artisan migrate --path=Modules/Miscellaneous/Database/Migrations/2022_12_28_114416_create_notification_cron_trackings_table.php
php artisan migrate --path=Modules/Miscellaneous/Database/Migrations/2022_12_28_114439_create_notification_cron_process_table.php

php artisan migrate --path=Modules/User/Database/Migrations/2023_02_24_173327_create_hub_info_icons_table.php
php artisan migrate --path=Modules/User/Database/Migrations/2023_02_24_141600_create_report_user__table.php

ALTER TABLE `marketplace_products` CHANGE `min_order_quantity` `min_order_quantity` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `marketplace_products` CHANGE `product_price` `product_price` DECIMAL(10,2) NULL;
ALTER TABLE `marketplace_products` CHANGE `quantity_available` `quantity_available` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_19_171411_create_marketplace_orders_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_19_173509_create_marketplace_taxes_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_19_173529_create_marketplace_tax_classes_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_19_174839_create_marketplace_order_items_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_20_102744_create_marketplace_order_shipping_addresses_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_20_102926_create_marketplace_order_item_taxes_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_20_103236_create_marketplace_order_transactions_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_21_102917_create_marketplace_map_calss_taxes_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_21_104207_create_marketplace_user_address_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_21_104329_create_marketplace_product_offers_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_06_21_110446_create_marketplace_map_product_offers_table.php
ALTER TABLE `marketplace_products` ADD `tax_class_id` BIGINT NULL DEFAULT NULL AFTER `status`;
ALTER TABLE `marketplace_order_items` ADD `offer_map_id` BIGINT NULL DEFAULT NULL AFTER `tax_class_id`;
ALTER TABLE `marketplace_order_shipping_addresses` ADD `email` VARCHAR(250) NULL DEFAULT NULL AFTER `last_name`;
ALTER TABLE `marketplace_user_address` ADD `email` VARCHAR(250) NULL DEFAULT NULL AFTER `last_name`;
ALTER TABLE `marketplace_user_address` ADD `street_address_2` TEXT NULL DEFAULT NULL AFTER `street_address`;
ALTER TABLE `marketplace_order_shipping_addresses` ADD `street_address_2` TEXT NULL DEFAULT NULL AFTER `street_address`;
php artisan migrate --path=Modules/Recipe/Database/Migrations/2023_06_27_153343_create_unit_quantities_table.php
php artisan migrate --path=Modules/Marketplace/Database/Migrations/2023_07_17_171353_create_payment_settings_table.php
php artisan migrate --path=Modules/User/Database/Migrations/2023_08_17_111351_create_discovery_post_categories_table.php
php artisan migrate --path=Modules/User/Database/Migrations/2023_08_17_111454_create_discovery_posts_table.php
php artisan migrate --path=Modules/User/Database/Migrations/2023_10_11_144107_create_send_connection_request_hubs_table.php
php artisan migrate --path=Modules/Activity/Database/Migrations/2023_10_20_102612_create_discoveryNewsView_table.php
