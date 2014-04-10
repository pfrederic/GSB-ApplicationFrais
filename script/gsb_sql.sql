drop database GSB;
create database GSB;
use GSB;
grant all on GSB.* to technicien@'%' identified by 'ini01';
source gsb_frais_structure.sql
source gsb_frais_insert_tables_statiques.sql
