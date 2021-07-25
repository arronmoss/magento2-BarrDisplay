SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

BEGIN;
INSERT INTO `inventory_stock` VALUES (2, 'barrdisplay');
UPDATE inventory_stock_sales_channel SET stock_id=2 WHERE 1;

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
