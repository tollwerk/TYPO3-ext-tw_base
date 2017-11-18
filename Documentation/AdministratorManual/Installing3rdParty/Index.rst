.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _3rd-party:

Installing 3rd-party software
=============================

The extension brings several services that depend on external software. In order to use these services you need to ensure that the necessary software is available on your server.

File compressors
----------------

The extension registers several services that improve performance by compressing various file formats. Depending on the file formats, you need to install different tools for this.

JPEG images: mozjpeg
^^^^^^^^^^^^^^^^^^^^

Please read at https://blarg.co.uk/blog/how-to-install-mozjpeg for instructions on how to install **mozjpeg** on your system. After the installation, please rename the newly installed ``jpegtran`` binary to ``mozjpeg`` and make sure that it's globally executable on your system. On Linux you can do this by:

.. code-block:: bash

    ln -s /path/to/jpegtran /usr/local/bin/mozjpeg

The **mozjpeg** compressor hooks itself into the TYPO3 image manipulation process. As soon as you enable the compressor via the TypoScript constant ``compressors.mozjpeg``, every JPEG image processed by TYPO3 will be compressed with the mozjpeg encoder.

SVG vector graphics: SVGO
^^^^^^^^^^^^^^^^^^^^^^^^^

Please read at https://github.com/svg/svgo for instructions on how to install **SVGO** on your system. After the installation, please make sure that the ``svgo`` binary is globally executable on your system.

The **svgo** compressor hooks itself into the TYPO3 image manipulation process. As soon as you enable the compressor via the TypoScript constant ``compressors.svgo``, every SVG vector graphic processed by TYPO3 will be compressed by SVGO.

You can `configure SVGO <https://github.com/svg/svgo/blob/master/docs/how-it-works/en.md#1-config>`_ using the TypoScript key ``plugin.tx_twbase.settings.images.compress.svg`` like so:

.. code-block:: typoscript

    plugin.tx_twbase.settings.images.svg {
        plugins {
            0 {
                removeViewBox = 0
            }
        }
    }
