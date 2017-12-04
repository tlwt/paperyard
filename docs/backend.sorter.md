# sorter

once you confirmed that the document has been detected correctly the sorter will put it into specified directories or trigger pre-defined actions (e.g. mail it so somebody)

## flow description

the program roughly executes as follows.

1. database connection is established.
1. the base class is being instanciated to work provide output and other supporting functionalities
1. the program then goes into ```/data/sort``` (a local folder needs to be mapped via ```docker run``` command) and looks for ```*.pdf``` files

**foreach found pdf** the following flow is executed:

1. the file name is split up into its parts
   * date
   * company
   * subject
   * recipient
   * amount
   * tags
 2. based on the gathered data paperyard checks archive / sort rules
