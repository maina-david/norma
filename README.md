# Norma Monolith

## Local Debugging

### PHPStorm

 - Ensure that the **Docker** and **PHP Remote Interpreter** plugins are enabled.
 - Go to **Settings > PHP** and add a new **Docker Interpreter** for sail.
 - Save the changes and in the **Interpreter** view, select the folder next to the **Docker Container** field
 - Update the **Container Path** in the dialog that opens and set it to `/var/www/html`
 - Go to **Settings > PHP > Servers** and add a new server:
     - Name: **Docker**
     - Host: **0.0.0.0**
     - Port: **80**
     - Debugger: **xdebug**
     - Use path mapping: **checked**
     - Set the project root to have the absolute path in the server as `/var/www/html`
 - Save and close
 - Enable the debugger and test it out.
