# MonologToLoggly

Upload monolog log files to Loggly.

### Installation
- composer install
- copy `config.example.json` as `config.json` and put there your loggly key.

### Execution
- Edit the `start.php` file and provide the path to one of your log files.
- call the script `php start.php`

### Example SFTP transfer
If your log files are on a different server, you can automate the log file transfer using the `sftp` command. Create a file called `sftp_commands.txt` and put the command that should be executed after connection:

```txt
cd websites/project/logs
get 2018-10-06.log
exit
```
Then you can call the `sftp` command with that file:

```sh
sftp -b sftp_commands.txt username@example.com
```