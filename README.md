# GBV-CSL

This repository contains 

* an API to query bibliographic databases from GBV and return the results in a
  JSON structure appropriate to be formatted with [Citation Style Language](
  http://citationstyles.org/) (CSL).

* an API to get CSL styles and locales, packed in JSON for processing with
  [citeproc-js](http://gsl-nagoya-u.net/http/pub/citeproc-doc.html) in a 
  client browser.

* a web application that makes use of the APIs to search and display list of
  citations (bibliographic references).

A public beta-version of this service for testing, feedback, a development, is
available at <http://ws.gbv.de/csl/>. The source code is available in GitHub at

* https://github.com/gbv/gbv-csl

Feel free to clone the repository for testing and reuse under the terms of
AGLP. Bugfixes and extensions are very welcome! Issues are tracked at

* https://github.com/gbv/gbv-csl/issues


# Installation

Clone this repository into a directory of your choice and update submodules:

    git clone git://github.com/gbv/gbv-csl.git
    git submodule update --init

This will download the official CSL citation style repository and standard CSL
locale files into folder `styles` and `locales`, respectively, and a mirror of
citeproc-php into folder `citeproc-php`.

Then put the directory at a webserver with PHP >= 5.3 and open in your browser.

# Copyright and license

Copyright 2013 Verbundzentrale des GBV (VZG)

Licensed under the [GNU Affero General Public
License](http://www.gnu.org/licenses/agpl-3.0.html) (AGPL).

Contains parts of [Twitter Bootstrap](http://twitter.github.com/bootstrap/),
originally licensed under the Apache License, Version 2.0.

