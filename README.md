# HwtTypo3HwtImporthandler

### About:  
This TYPO3 extension provides a backend module for import handling since TYPO3 6.2. You can setup selectable presets for import routines and give your users the possibility to change configuration options. The basic principle how to handle the involved components is inspired by the TYPO3 formhandler extension but stripped-down to a universal import process.


### Features:

**Conceptual + Backend**

- Setup import configuration and steps via TypoScript settings.
- Define configuration options which can be used by backend users.
- Define your import routine(s) with one or more steps
- Define an import routine as default for quickstart in first view.
- Easy extend/add your own configurators and importers with php
- Namespaced Extbase extension

**Integration**

- Installable via Composer


## Installation:
The currently released versions are available in the TYPO3 Extension Repository (TER) or via composer. So you can 
 - download and install the extension with the extension manager inside the TYPO3 Backend

or

- do `composer require tieupmedia/hwt_importhandler` from your console

Further you can manually get the versioned source code from github and manually upload the extension. If you do so, name the extension folder "hwt_importhandler" (don't keep the git library name)!


## Versions:
- \>= 0.0.2 for TYPO3 6.2 - 7.6
- \>= 0.1.0 for TYPO3 7.6 - 8.7