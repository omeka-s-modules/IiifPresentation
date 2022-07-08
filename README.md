# IIIF Presentation

An [Omeka S](https://omeka.org/s/) module that implements the [IIIF Presentation API](https://iiif.io/api/presentation/3.0/).

## Events

This module triggers these events during the composition of certain IIIF Presentation
resources (manifest, canvas, collection, etc.). Use the event's `getTarget()` method
to get the current controller.

### IIIF Presentation v2

These events are available for version 2 of the IIIF Presentation API.

#### `iiif_presentation.2.media.canvas`

- `canvas`: The canvas array
- `canvas_type`: The canvas type service object
- `media_id`: The media ID

Triggered after composing a media canvas array. To modify the canvas, handlers may
modify the `canvas` parameter and set it back to the event.

#### `iiif_presentation.2.item.manifest`

- `manifest`: The manifest array
- `item_id`: The item ID

Triggered after composing an item manifest array. To modify the manifest, handlers
may modify the `manifest` parameter and set it back to the event.

#### `iiif_presentation.2.item.collection`

- `collection`: The collection array
- `item_ids`: The item IDs in the collection

Triggered after composing an item collection array. To modify the collection, handlers
may modify the `collection` parameter and set it back to the event.

#### `iiif_presentation.2.item_set.collection`

- `collection`: The collection array
- `item_set_id`: The item set ID

Triggered after composing an item set collection array. To modify the collection,
handlers may modify the `collection` parameter and set it back to the event.

#### `iiif_presentation.2.item_set.collections`

- `collection`: The collection array
- `item_set_ids`: The item set IDs in the collection

Triggered after composing an item set collections array. To modify the collection,
handlers may modify the `collection` parameter and set it back to the event.

### IIIF Presentation v3

These events are available for version 3 of the IIIF Presentation API.

#### `iiif_presentation.3.media.canvas`

- `canvas`: The canvas array
- `canvas_type`: The canvas type service object
- `media_id`: The media ID

Triggered after composing a media canvas array. To modify the canvas, handlers may
modify the `canvas` parameter and set it back to the event.

#### `iiif_presentation.3.item.manifest`

- `manifest`: The manifest array
- `item_id`: The item ID

Triggered after composing an item manifest array. To modify the manifest, handlers
may modify the `manifest` parameter and set it back to the event.

#### `iiif_presentation.3.item.collection`

- `collection`: The collection array
- `item_ids`: The item IDs in the collection

Triggered after composing an item collection array. To modify the collection, handlers
may modify the `collection` parameter and set it back to the event.

#### `iiif_presentation.3.item_set.collection`

- `collection`: The collection array
- `item_set_id`: The item set ID

Triggered after composing an item set collection array. To modify the collection,
handlers may modify the `collection` parameter and set it back to the event.

#### `iiif_presentation.3.item_set.collections`

- `collection`: The collection array
- `item_set_ids`: The item set IDs in the collection

Triggered after composing an item set collections array. To modify the collection,
handlers may modify the `collection` parameter and set it back to the event.

# Copyright

IiifPresentation is Copyright © 2021-present Corporation for Digital Scholarship,
Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code under
the GNU General Public License, version 3 (GPLv3). The full text of this license
is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.
