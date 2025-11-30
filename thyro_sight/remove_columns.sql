-- Remove unnecessary columns from healtha table

ALTER TABLE healtha DROP COLUMN IF EXISTS `thyroxine`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `advised_thyroxine`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `antithyroid`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `illness`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `pregnant`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `surgery`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `radioactive`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `hypo_suspected`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `hyper_suspected`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `lithium`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `goitre`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `tumor`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `hypopituitarism`;
ALTER TABLE healtha DROP COLUMN IF EXISTS `psychiatric`;
