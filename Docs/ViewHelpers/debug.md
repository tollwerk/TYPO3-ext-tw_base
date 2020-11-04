# TYPO3 extension: tw_base

> Collection of building blocks and viewhelpers for TYPO3 projects by tollwerk

## `debug` viewhelper

The `<base:debug>`-ViewHelper works exactly like `<f:debug>`, but checks the user's IP address
against `$GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']` first. If the user's IP address is not found there and
does not contain wildcards, there will be no debugging output. With this, you can debug fluid templates inside live 
environments without regular users noticing it.



