# CLI Tool
Paperyard offers a very simple cli tool to handle some tasks which would otherwise require more handwork. The tool can be activated with the `paperyard_util` parameter in the configuration.

## Usage
``` 
usage: paperyard <realm>:<command> [<options>]
use commands:list to get a full list
```
All commands are grouped into realms. Some commands need additional parameters (options).

## Commands

### `users:list`
Lists all users.

### `users:add <username> <password>`
Creates a new user.  
The parameters support a large set of special characters on the database side. We still recommend to only use alpha-numeric usernames and passwords. This is due to the uncertainty on how your shell or browser will handle these special characters. See [Authentication] for more informations on access control security.

### `users:delete <username>`
Removes a user.

### `database:version`
Returns current database version.

### `database:config`
Lists all config variables and there values.

### `database:update`
Starts native update process.

### `commands:list`
Lists all commands.