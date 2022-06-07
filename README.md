# PCEBookShop PM4 [![Poggit-CI](https://poggit.pmmp.io/shield.dl/PCEBookShop)](https://poggit.pmmp.io/p/PCEBookShop) [![Discord](https://img.shields.io/discord/330850307607363585?logo=discord)](https://discord.gg/qmnDsSD)

PCEBookShop is an extension to the [PiggyCustomEnchants](https://github.com/DaPigGuy/PiggyCustomEnchants/) plugin which adds a custom enchants book shop.

### Quick Tip for Players
* You can validate the authenticity of a custom enchants book (which has not been activated yet) by running `/ce nbt`.
    * Normal players are able to run this command by default.
    * Near the bottom, you might find a line that looks similar to this: `pocketmine\nbt\tag\IntTag: name='pcebookshop', value='1'`.
        * Value 1 = Mythic
        * Value 2 = Rare
        * Value 5 = Uncommon
        * Value 10 = Common
    * Optionally, you can just look at the item lore.    
    * You won't be able to find this tag once the item has been activated, but rather a tag created by PiggyCustomEnchants.

## Supported Economy Types
  * PMMP Player EXP
  * SUPPORT FOR ECONOMYAPI AND BEDROCKECONOMY SOON.

## Commands
| Command | Description | Permissions | Aliases |
| --- | --- | --- | --- |
| `/pcebookshop` | Opens the PCEBookShop Menu | `pcebookshop.command.bookshop` | `/bookshop, /bs` |

## Permissions
| Permissions | Description | Default |
| --- | --- | --- |
| `pcebookshop` | Allows usage of all PCEBookShop features | `op` |
| `pcebookshop.command` | Allow usage of all PCEBookShop commands | `op` |
| `pcebookshop.command.bookshop` | Allow usage of the /pcebookshop command | `true` |

## License
```
    PCEBookShop for PiggyCustomEnchants.
    Copyright (C) 2020  Aericio

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
```
