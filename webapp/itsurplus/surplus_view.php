<?php
die;
require_once 'autoload.php';

$sql = "
	CREATE OR REPLACE VIEW v_surplus_computers
	AS
    SELECT c.id,                                                                                                                                                                                                                                                              
           c.name AS psu_name,                                                                                                                                                                                                                                                
           c.serial,                                                                                                                                                                                                                                                          
           c.notepad AS notes,                                                                                                                                                                                                                                                
           s.name AS state,                                                                                                                                                                                                                                                   
           `mod`.name AS model,                                                                                                                                                                                                                                               
           man.name AS manufacturer,
           os.name AS os,
           osv.name AS os_version,
           oss.name AS os_service_pack,                                                                                                                                                                                                                                          
           t.name AS `type`                                                                                                                                                                                                                                                   
    FROM glpi_computers c,                                                                                                                                                                                                                                                    
         glpi_states s,                                                                                                                                                                                                                                                       
         glpi_computermodels `mod`,                                                                                                                                                                                                                                           
         glpi_manufacturers man,
         glpi_operatingsystems os,
         glpi_operatingsystemversions osv,
         glpi_operatingsystemservicepacks oss,                                                                                                                                                                                                                                              
         glpi_computertypes t                                                                                                                                                                                                                                                 
    WHERE c.states_id = s.id                                                                                                                                                                                                                                                  
      AND s.name = 'Surplused'                                                                                                                                                                                                                                                
      AND c.computermodels_id = `mod`.id                                                                                                                                                                                                                                      
      AND c.manufacturers_id = man.id                                                                                                                                                                                                                                         
      AND c.computertypes_id = t.id
      AND c.operatingsystems_id = os.id
      AND c.operatingsystemversions_id = osv.id
      AND c.operatingsystemservicepacks_id = oss.id  
";
