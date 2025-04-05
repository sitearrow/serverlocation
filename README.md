# Free Server Location Hook for WHMCS
Free Server Location Hook for WHMCS, version 1.0

## Install Instructions
1. Upload the location.php to /includes/hooks/ in your WHMCS install
2. In WHMCS, navigate to: System Settings > Servers and rename your servers to include the location code in brackets - eg `Server01 (US-East)` or `Server02 (Asia)`
3. Create a Configurable Options group called "Server Location" (or similar, the name is not important for the hook)
4. Create an option inside that group called "Available Region" (or update the value of $serverCriteria to the name of the option you have chosen), as a Radio or Dropdown option (your choice)
5. Name the value `location code|Human Readable Name for location` eg `US-East|US East (New York)` or `Asia|Asia - Singapore`. You can set pricing if you wish to charge more for services hosted in this location, eg if you want to charge a surcharge for hosting in Asia due to higher server costs compared to US/Europe.
6. Assign the Configurable Options group to the cPanel/DirectAdmin hosting products that you wish to offer multiple locations for.

## How it works
It uses the `AfterShoppingCartCheckout` hook in WHMCS to update the server ID in the database for the service based on the value selected in the configurable option.

The hook will allocate the account to the server with the lowest number of accounts on it that isn't set to Disabled in WHMCS. 

This means that it does not take into account the "Maximum Number of Accounts" value or "Utilisation %" in WHMCS at this stage.

## Fair Use Notice
This project is licensed under the GPLv3. Redistribution, commercial usage, and modifications are allowed *only if* the full source code is made available under the same license. Rebranding or reselling without significant contribution is strongly discouraged.
