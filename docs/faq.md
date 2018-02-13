this section contains frequently asked questions (FAQs) and errors other users have run into when setting up the project.

## FAQ

### Address already in use - you already run a web server

Something like the following error will be thrown if you already have a webserver running on port 80

```
Error response from daemon: driver failed programming external connectivity on endpoint ppyrd: 
Error starting userland proxy: listen tcp 0.0.0.0:80: bind: address already in use.
```

#### Solution
Either you stop the currently running webserver before launching the container or you change ```docker-compose.yml``` (https://github.com/tlwt/paperyard/blob/master/docker-compose.yml) 

```
    ports:
     - "80:80"
```

and set it to some available port (note that 60080 may also be blocked by some other app)

```
    ports:
     - "60080:80"
```

