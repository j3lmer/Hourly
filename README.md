# BOUW7_PROGRAM


This is a program which can be used as a time tracking tool.

The user can make new projects, and within that project, add hour entries.

  Hour entries exist out of a timestamp start, timestamp end, and a duration which is calculated from the beforementioned timestamps.
  
Users can delete specific hour entries or the entire project, if needed.

Users can also modify specific project names or change timestamps in entries.


# Setup

Make sure you have a database running, im personally using xampp with mysql.

Modify the .env to your specific needs, see: https://symfony.com/doc/current/configuration.html#configuration-based-on-environment-variables.

Open a terminal/cli and change directory to the directory where this program is located and start the symfony server with the following command: 
```symfony serve```.


A possible addition to this command is the -d tag, which makes it so the process runs in the background.

If needed, the symfony server can be stopped using the following command:
```symfony server:stop```.
