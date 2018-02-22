# Authentication
Paperyard utilises http basic auth for access control. Please read [Security](#security)

## Activate
To make use of this feature you first have to activate the Paperyard CLI tool. Open the Paperyard configuration file `/config/paperyard`. Find the _paperyard_util_ parameter and remove the command symbol `#` from the example.

Start or restart your container. Type `paperyard` and press enter. The output should look similar to this:
```
Paperyard CLI Utility
usage: paperyard <realm>:<command> [<options>]
use commands:list to get a full list
```
Now you can add a user. This will activate access control. Type `paperyard users:add <username> <password>`, substitute your credentials and hit enter.

Paperyard will now ask for login credentials on the beginning of each session. You can add more users if needed.

## Deactivate
To deactivate this feature, all users must be deleted. Get a list with all users by running `paperyard users:list` and delete each with `paperyard users:delete <username>`.

## Limitations
Access control is only realised by using http basic auth. This is not an replaced for a real user manament. Gradiual access restriciton to users and groups is not possible.

## Security
Http basic auth provides no confidentiality protection. Therefore, https has to be used on every host other then _localhost_.
