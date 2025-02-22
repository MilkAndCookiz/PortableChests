# PortableChests - PocketMine Plugin

A PocketMine-MP plugin that allows players to convert chests into portable inventory storage and place them anywhere while retaining their contents.

## Features
- Convert any chest into a portable chest item (sneak + right-click).
- Place portable chests while preserving their original inventory.
- Configurable messages and chest names.
- Permission-based access control.
- Prevents inventory loss during pickup/placement.

## Installation
1. Download the latest `PortableChests.phar` from [Releases](#).
2. Place the `.phar` file in your server's `plugins` folder.
3. Restart the server.

## Configuration
Edit `config.yml` in the `PortableChests` folder after first run:

```yaml
# Messages configuration
messages:
  chest_made_portable: "&aChest has been made portable!"
  chest_placed: "&aPortable chest has been placed!"
  not_authorized_pickup: "&cYou can't make chests portable here!"
  not_authorized_place: "&cYou can't place portable chests here!"
  inventory_full: "&cYour inventory is full!"

# Chest appearance
chest-name-color: "&6" # Color codes allowed
chest-name: "Portable Chest"
