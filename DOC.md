# Documentation

## Install the Laravel scheduler service

The unit file lives at `deploy/laravel-schedule.service`. It runs `php artisan schedule:work` as a long-running process — no system cron entry needed.

```bash
sudo cp deploy/laravel-schedule.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-schedule.service
```

Verify it came up:

```bash
systemctl status laravel-schedule.service
journalctl -u laravel-schedule.service -f
```

After editing the unit file, reload and restart:

```bash
sudo cp deploy/laravel-schedule.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl restart laravel-schedule.service
```

> Do **not** also add `* * * * * php artisan schedule:run` to crontab — with `schedule:work` running, scheduled tasks would fire twice.

## Install the Laravel queue worker service

The unit file lives at `deploy/laravel-queue.service`. It runs `php artisan queue:work` and restarts it hourly to free leaked memory.

```bash
sudo cp deploy/laravel-queue.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now laravel-queue.service
```

Verify it came up:

```bash
systemctl status laravel-queue.service
journalctl -u laravel-queue.service -f
```

After editing the unit file, reload and restart:

```bash
sudo cp deploy/laravel-queue.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl restart laravel-queue.service
```

After deploying new code, tell the worker to pick it up (the unit's `ExecStop` already calls this on stop, but you can trigger it any time):

```bash
php artisan queue:restart
```

## List systemd services

```bash
systemctl list-units --type=service                 # active services only
systemctl list-units --type=service --all           # include inactive/failed
systemctl list-unit-files --type=service            # every installed unit + enabled state
```

### Filter

```bash
systemctl list-units --type=service --state=running       # only currently running
systemctl list-units --type=service --state=failed        # only failed
systemctl list-unit-files --type=service --state=enabled  # only enabled at boot
systemctl list-units --type=service 'laravel-*'           # glob match (our units)
```

### Inspect one

```bash
systemctl status laravel-schedule.service           # state + recent logs
systemctl is-active laravel-schedule.service        # active / inactive
systemctl is-enabled laravel-schedule.service       # enabled / disabled
systemctl cat laravel-schedule.service              # show the unit file as loaded
journalctl -u laravel-schedule.service -f           # follow live logs
```

### Tip

`systemctl` pages output through `less`. Add `--no-pager` for scripts, or `| cat` to dump everything:

```bash
systemctl list-units --type=service --no-pager
```
