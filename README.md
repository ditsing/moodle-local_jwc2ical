将教务处发布的课程表导入Moodle日历
==================================

由管理员导入，学生在日历中直接就能看到

导入方法
--------

每学期开学：

```bash
sudo -u www-data php local/jwc2ical/cli/sync.php -d
sudo -u www-data php local/jwc2ical/cli/sync.php -i
```
