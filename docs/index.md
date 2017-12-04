Welcome to paperyard's documentation!

## What is paperyard
It is a tool which takes a scanned document, turns it into a searchable PDF documents and builds up a meaningful name based on the content of the document.

## What does it consist of

Since paperyard uses multiple existing tools and would be difficult to set up itself, we utilize docker to combine all tooling.

Most of the code is written in PHP.

## Quick start

* Install [docker](https://www.docker.com)
  * > Docker is a tool that can package an application and its dependencies in a virtual container that can run on any Linux server. This helps enable flexibility and portability on where the application can run, whether on premises, public cloud, private cloud, bare metal, etc. [LinuxMag](https://www.linux.com/news/docker-shipping-container-linux-code)
* download [dockerRun.sh](the https://raw.githubusercontent.com/tlwt/paperyard/master/dockerRun.sh)
* edit the ```dockerRun.sh``` to meet your needs (or rename it to .bat for windows)
* run the ```dockerRun.sh```
* open your browser and point it to (http://localhost) => follow the instructions you see there


## Links

* the source code can be found at: [Github](https://github.com/tlwt/paperyard/)
* The code documentation (doxygen) can be found at: [Github](https://tlwt.github.io/paperyard/index.html)

## Authors
* [Jannik Kramer](kramer@consider-it.de)
* [Till Witt](witt@consider-it.de)

## License

[See here](https://github.com/tlwt/paperyard#license)
