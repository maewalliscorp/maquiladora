-- Add pdf_path column to mrp_oc table
ALTER TABLE `mrp_oc` ADD COLUMN `pdf_path` VARCHAR(255) NULL AFTER `eta`;
