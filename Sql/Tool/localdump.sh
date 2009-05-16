#!/bin/sh

mysqldump -u root --no-data --default-character-set=utf8 --add-drop-table --add-drop-database --comments --quote-names --set-charset hashmark_test categories categories_milestones categories_scalars milestones samples_string samples_decimal samples_analyst_temp scalars | sed 's/AUTO_INCREMENT=[0-9]*/AUTO_INCREMENT=1/g'
