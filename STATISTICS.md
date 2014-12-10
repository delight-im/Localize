# Statistics

## Projects with most activity in past 14 days

```
SELECT id, name, FROM_UNIXTIME(creation_date) AS since, (SELECT COUNT(*) FROM translationSessions WHERE repositoryID = repositories.id AND timeStart > (UNIX_TIMESTAMP()-3600*24*14)) AS contributions FROM repositories ORDER BY contributions DESC LIMIT 0, 50
```
