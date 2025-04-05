<?php
/*
Server Location Hook for WHMCS
https://github.com/sitearrow/serverlocation
*/

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;

function serverLocation_selectServer($params)
{
    $serviceId = $params['serviceid'];
    
    // Get the configured option for server selection
    $serverCriteria = 'Available Region'; // Replace with the actual config option key
    
    $result = Capsule::table('tblproductconfigoptions')
        ->join('tblproductconfigoptionssub', 'tblproductconfigoptions.id', '=', 'tblproductconfigoptionssub.configid')
        ->join('tblhostingconfigoptions', 'tblproductconfigoptionssub.id', '=', 'tblhostingconfigoptions.optionid')
        ->where('tblhostingconfigoptions.relid', $serviceId)
        ->where('tblproductconfigoptions.optionname', $serverCriteria)
        ->select('tblproductconfigoptionssub.optionname')
        ->first();
    
    if (!$result) {
        logActivity("Server Allocator: No matching region found for service ID {$params['serviceid']}");
        return;
    }

    $selectedOption = $result->optionname;
    
    // Extract region code from option value (e.g., "SG" from "SG|Oceania (Singapore)")
    $regionCode = explode('|', $selectedOption)[0];
    
    // Fetch least used server in the selected region
    $server = Capsule::table('tblservers')
        ->leftJoin('tblhosting', 'tblservers.id', '=', 'tblhosting.server')
        ->select('tblservers.id')
        ->where('tblservers.disabled', 0)
        ->where('tblservers.name', 'like', "%($regionCode)%")
        ->groupBy('tblservers.id')
        ->orderByRaw('COUNT(tblhosting.id) ASC')
        ->first();
    
    if (!$server) {
        logActivity("Server Allocator: No available servers found for region {$regionCode} for service ID {$params['serviceid']}");
        return;
    }
    
    Capsule::table('tblhosting')
        ->where('id', $params['serviceid'])
        ->update(['server' => $server->id]);

    logActivity("Server Allocator: Assigned service ID {$params['serviceid']} to server {$server->name} (ID: {$server->id}) in region {$regionCode}");

}

add_hook('AfterShoppingCartCheckout', 1, function($params) {
    if (!empty($params['ServiceIDs'])) {
        foreach ($params['ServiceIDs'] as $serviceId) {
            serverLocation_selectServer(['serviceid' => $serviceId]);
        }
    }
});
