pictureWidth
~~~~~~~~~~~~

**type**: `integer`
**default**: |default_pictureWidth|

Define the width of the thumbnail in pixels. The default value is ``60``.
If the column the column is in is smaller than this value, the thumbnail will be resized to fit the column.

.. code-block:: yaml

    columns:
        myColumn:
            pictureWidth: 60
            # ... further option